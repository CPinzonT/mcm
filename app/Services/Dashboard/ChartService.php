<?php

namespace App\Services\Dashboard;

use App\Data\DashboardFiltersData;
use App\Services\Dashboard\Concerns\AppliesOperativePortfolioDocuments;
use App\Services\Dashboard\Concerns\AppliesPortfolioPeriodCut;
use App\Services\Risk\Concerns\AppliesLiveDaysOverdue;
use App\Services\Risk\RiskClassificationService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

/**
 * Prepara los datasets de Chart.js para los 7 gráficos del dashboard.
 *
 * Todos los métodos retornan un array compatible con Chart.js 4:
 * ['labels' => [...], 'datasets' => [...]]
 *
 * SUPUESTOS:
 * - Aging agrupa por riesgo según días de mora vivos (vencimiento → fecha de consulta).
 * - Tendencia agrupa por mes de issue_date (contabilización) en el último corte.
 * - Pareto ordena por pending_amount desc, Top 10.
 */
class ChartService
{
    use AppliesLiveDaysOverdue;
    use AppliesOperativePortfolioDocuments;
    use AppliesPortfolioPeriodCut;
    private const RISK_COLORS = [
        'normal'   => '#10b981',
        'low'      => '#3b82f6',
        'medium'   => '#f59e0b',
        'high'     => '#f97316',
        'critical' => '#ef4444',
    ];

    private const RISK_LABELS = [
        'normal'   => 'Corriente (sin mora)',
        'low'      => 'Bajo (1-30d post-venc.)',
        'medium'   => 'Medio (31-60d)',
        'high'     => 'Alto (61-90d)',
        'critical' => 'Crítico (91+d)',
    ];

    public function compute(DashboardFiltersData $filters): array
    {
        return [
            'aging'       => $this->aging($filters),
            'trend'       => $this->trend($filters),
            'by_uen'      => $this->byDimension($filters, 'c.uen', 'UEN'),
            'by_channel'  => $this->byDimension($filters, 'c.channel', 'Canal'),
            'by_advisor'  => $this->byAdvisor($filters),
            'pareto'      => $this->pareto($filters),
        ];
    }

    // ── 1. Distribución por edad (aging) ──────────────────────────────────

    public function aging(DashboardFiltersData $filters): array
    {
        $keys = ['actual', '1_30_dias', '31_60_dias', '61_90_dias', '91_180_dias', '181_360_dias', '361_dias'];
        $labels = [
            'Corriente',
            '1-30 días',
            '31-60 días',
            '61-90 días',
            '91-180 días',
            '181-360 días',
            '+360 días',
        ];
        $colors = [
            '#10b981',
            '#3b82f6',
            '#6366f1',
            '#f59e0b',
            '#f97316',
            '#ef4444',
            '#991b1b',
        ];

        $totals = array_fill_keys($keys, 0.0);
        $risk = app(RiskClassificationService::class);
        $asOf = CarbonImmutable::parse($filters->consultationDate());

        $q = $this->baseActiveQuery($filters);
        $this->applyPortfolioBalanceStatus($q);
        $rows = $q->select('pd.pending_amount', 'pd.days_overdue', 'pd.due_date', 'pd.aging_buckets')
            ->get();

        foreach ($rows as $row) {
            $amount = (float) $row->pending_amount;
            if (abs($amount) < 0.0001) {
                continue;
            }

            $bucketKey = $this->resolveAgingBucketKey($row, $risk, $asOf);

            if ($bucketKey !== null) {
                $totals[$bucketKey] += $amount;
            }
        }

        $grand = array_sum($totals);
        $pcts = array_map(fn ($v) => $grand > 0 ? round($v / $grand * 100, 1) : 0, array_values($totals));

        return [
            'labels' => $labels,
            'bucket_keys' => $keys,
            'datasets' => [
                [
                    'label' => 'Saldo pendiente',
                    'data' => array_values($totals),
                    'backgroundColor' => $colors,
                    'borderColor' => '#ffffff',
                    'borderWidth' => 2,
                ],
            ],
            'pcts' => $pcts,
        ];
    }

    private function resolveAgingBucketKey(object $row, RiskClassificationService $risk, CarbonImmutable $asOf): ?string
    {
        $buckets = json_decode((string) ($row->aging_buckets ?? ''), true);
        if (is_array($buckets)) {
            foreach ([
                'actual', '1_30_dias', '31_60_dias', '61_90_dias', '91_180_dias', '181_360_dias', '361_dias',
            ] as $key) {
                if (abs((float) ($buckets[$key] ?? 0)) > 0.0001) {
                    return $key;
                }
            }
        }

        $days = (int) ($row->days_overdue ?? 0);
        if ($row->due_date) {
            $days = $risk->daysOverdueAsOf(CarbonImmutable::parse($row->due_date), $asOf);
        }

        return match (true) {
            (float) $row->pending_amount <= 0, $days <= 0 => 'actual',
            $days <= 30 => '1_30_dias',
            $days <= 60 => '31_60_dias',
            $days <= 90 => '61_90_dias',
            $days <= 180 => '91_180_dias',
            $days <= 360 => '181_360_dias',
            default => '361_dias',
        };
    }

    // ── 2. Tendencia de exposición por período ────────────────────────────

    public function trend(DashboardFiltersData $filters): array
    {
        $q = $this->baseActiveQuery($filters);
        $this->applyPortfolioBalanceStatus($q);
        $rows = $q->whereNotNull('pd.issue_date')
            ->select(
                DB::raw("LEFT(pd.issue_date, 7) as ym"),
                DB::raw('SUM(pd.pending_amount) as total')
            )
            ->groupBy('ym')
            ->orderBy('ym')
            ->get();

        $yms    = $rows->pluck('ym')->toArray();
        $labels = array_map(fn ($ym) => \Carbon\Carbon::parse($ym . '-01')->format('M Y'), $yms);
        $totals = $rows->map(fn ($r) => (float) $r->total)->toArray();
        $critical = $this->criticalTrend($filters, $yms);

        return [
            'labels'   => $labels,
            'yms'      => $yms,
            'datasets' => [
                [
                    'label'           => 'Cartera Total',
                    'data'            => $totals,
                    'borderColor'     => '#2563eb',
                    'backgroundColor' => 'rgba(37,99,235,0.08)',
                    'tension'         => 0.4,
                    'fill'            => true,
                    'yAxisID'         => 'y',
                ],
                [
                    'label'           => 'Cartera Crítica',
                    'data'            => $critical,
                    'borderColor'     => '#ef4444',
                    'backgroundColor' => 'rgba(239,68,68,0.06)',
                    'tension'         => 0.4,
                    'fill'            => false,
                    'yAxisID'         => 'y',
                    'borderDash'      => [5, 5],
                ],
            ],
        ];
    }

    private function criticalTrend(DashboardFiltersData $filters, array $yms): array
    {
        if (empty($yms)) return [];

        $q = $this->baseActiveQuery($filters)
            ->whereIn(DB::raw('LEFT(pd.issue_date, 7)'), $yms);
        $this->applyOperativeDocumentStatus($q);
        $this->whereLiveDaysOverdue($q, '>', 90, $filters);
        $rows = $q->select(
                DB::raw('LEFT(pd.issue_date, 7) as ym'),
                DB::raw('SUM(pd.pending_amount) as total')
            )
            ->groupBy('ym')
            ->get()
            ->keyBy('ym');

        return array_map(fn ($ym) => (float) ($rows[$ym]->total ?? 0), $yms);
    }

    // ── 3/4. Cartera vencida por dimensión (UEN / Canal) ──────────────────

    public function byDimension(DashboardFiltersData $filters, string $column, string $label): array
    {
        $q = $this->baseActiveQuery($filters)
            ->whereNotNull($column)
            ->where($column, '!=', '');
        $this->applyOperativeDocumentStatus($q);
        $this->whereLiveDaysOverdue($q, '>', 0, $filters);
        $rows = $q->select(DB::raw("{$column} as dim_value"), DB::raw('SUM(pd.pending_amount) as total'))
            ->groupBy($column)
            ->orderByDesc('total')
            ->get();

        $grand = (float) $rows->sum('total');
        $top   = $rows->take(10);

        $labels = $top->pluck('dim_value')->map(fn ($v) => $v ?: 'Sin ' . $label)->toArray();
        $totals = $top->pluck('total')->map(fn ($v) => (float) $v)->toArray();
        $pcts   = $top->map(fn ($row) => $grand > 0
            ? round((float) $row->total / $grand * 100, 1)
            : 0.0)->values()->all();

        return [
            'labels'   => $labels,
            'pcts'     => $pcts,
            'datasets' => [[
                'label'           => "Cartera Vencida por {$label}",
                'data'            => $totals,
                'backgroundColor' => '#f97316',
                'borderWidth'     => 0,
                'borderRadius'    => 4,
            ]],
        ];
    }

    // ── 5. Cartera vencida por asesor ─────────────────────────────────────

    public function byAdvisor(DashboardFiltersData $filters): array
    {
        $q = $this->baseActiveQuery($filters)->whereNotNull('a.name');
        $this->applyOperativeDocumentStatus($q);
        $this->whereLiveDaysOverdue($q, '>', 0, $filters);
        $rows = $q->select('a.id as advisor_id', 'a.name as advisor_name', DB::raw('SUM(pd.pending_amount) as total'))
            ->whereNotNull('a.name')
            ->groupBy('a.id', 'a.name')
            ->orderByDesc('total')
            ->get();

        $grand = (float) $rows->sum('total');
        $top   = $rows->take(12);

        $labels = $top->pluck('advisor_name')->map(fn ($v) => $v ?: 'Sin Asesor')->toArray();
        $totals = $top->pluck('total')->map(fn ($v) => (float) $v)->toArray();
        $pcts   = $top->map(fn ($row) => $grand > 0
            ? round((float) $row->total / $grand * 100, 1)
            : 0.0)->values()->all();
        $advisorIds = $top->pluck('advisor_id')->map(fn ($id) => $id !== null ? (int) $id : null)->toArray();

        return [
            'labels'      => $labels,
            'pcts'        => $pcts,
            'advisor_ids' => $advisorIds,
            'datasets' => [[
                'label'           => 'Cartera Vencida por Asesor',
                'data'            => $totals,
                'backgroundColor' => '#8b5cf6',
                'borderWidth'     => 0,
                'borderRadius'    => 4,
            ]],
        ];
    }

    // ── 6. Pareto de clientes (Top 10) ────────────────────────────────────

    public function pareto(DashboardFiltersData $filters): array
    {
        $rows = $this->baseActiveQuery($filters)
            ->select('pd.client_id', 'c.name as client_name', DB::raw('SUM(pd.pending_amount) as total'))
            ->groupBy('pd.client_id', 'c.name')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $grand   = (float) $rows->sum('total');
        $labels  = $rows->pluck('client_name')->toArray();
        $totals  = $rows->pluck('total')->map(fn ($v) => (float) $v)->toArray();

        $accumulated = 0;
        $cumPcts = array_map(function ($v) use ($grand, &$accumulated) {
            if ($grand <= 0) return 0;
            $accumulated += $v;
            return round($accumulated / $grand * 100, 1);
        }, $totals);

        $clientIds = $rows->pluck('client_id')->map(fn ($id) => (int) $id)->toArray();

        return [
            'labels'     => $labels,
            'client_ids' => $clientIds,
            'datasets'   => [
                [
                    'type'            => 'bar',
                    'label'           => 'Saldo Pendiente',
                    'data'            => $totals,
                    'backgroundColor' => '#2563eb',
                    'borderWidth'     => 0,
                    'borderRadius'    => 4,
                    'yAxisID'         => 'y',
                    'order'           => 2,
                ],
                [
                    'type'        => 'line',
                    'label'       => '% Acumulado',
                    'data'        => $cumPcts,
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => 'rgba(245,158,11,0.1)',
                    'tension'     => 0.3,
                    'yAxisID'     => 'y1',
                    'order'       => 1,
                    'pointRadius' => 4,
                    'pointBackgroundColor' => '#f59e0b',
                ],
            ],
        ];
    }

    // ── Query base ────────────────────────────────────────────────────────

    private function baseActiveQuery(DashboardFiltersData $filters)
    {
        $q = DB::table('portfolio_documents as pd')
            ->join('portfolio_loads as pl', 'pl.id', '=', 'pd.portfolio_load_id')
            ->join('clients as c', 'c.id', '=', 'pd.client_id')
            ->leftJoin('advisors as a', 'a.id', '=', 'pd.advisor_id')
            ->where('pl.status', 'completed')
            ->whereNull('pd.deleted_at');
        $this->applyDashboardPortfolioLoad($q);

        $this->applyPortfolioPeriodCut($q, $filters);
        $this->applyDimensionFilters($q, $filters);

        return $q;
    }

    private function applyDimensionFilters($q, DashboardFiltersData $filters): void
    {
        if (!empty($filters->uens)) {
            $vals = array_values(array_unique(array_map(static fn ($v) => trim((string) $v), $filters->uens)));
            $vals = array_values(array_filter($vals, static fn ($v) => $v !== ''));
            if ($vals !== []) {
                $q->whereIn(DB::raw('TRIM(c.uen)'), $vals);
            }
        } elseif ($filters->uen) {
            $q->whereRaw('TRIM(c.uen) = ?', [trim($filters->uen)]);
        }

        if (!empty($filters->regionals)) {
            $vals = array_values(array_unique(array_map(static fn ($v) => trim((string) $v), $filters->regionals)));
            $vals = array_values(array_filter($vals, static fn ($v) => $v !== ''));
            if ($vals !== []) {
                $q->whereIn(DB::raw('TRIM(c.region)'), $vals);
            }
        } elseif ($filters->regional) {
            $q->whereRaw('TRIM(c.region) = ?', [trim($filters->regional)]);
        }

        if (!empty($filters->channels)) {
            $vals = array_values(array_unique(array_map(static fn ($v) => trim((string) $v), $filters->channels)));
            $vals = array_values(array_filter($vals, static fn ($v) => $v !== ''));
            if ($vals !== []) {
                $q->whereIn(DB::raw('TRIM(c.channel)'), $vals);
            }
        } elseif ($filters->channel) {
            $q->whereRaw('TRIM(c.channel) = ?', [trim($filters->channel)]);
        }

        app(DashboardFilterCascadeService::class)->applyPortfolioAdvisorConstraint($q, $filters);

        if ($filters->clientId)           $q->where('pd.client_id', $filters->clientId);

        if (!empty($filters->riskLevels)) {
            $q->whereIn('pd.risk_level', $filters->riskLevels);
        }

        if (!empty($filters->documentTypes)) {
            $q->whereIn('pd.document_type', $filters->documentTypes);
        }
    }
}
