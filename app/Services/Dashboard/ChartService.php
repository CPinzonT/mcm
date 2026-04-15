<?php

namespace App\Services\Dashboard;

use App\Data\DashboardFiltersData;
use Illuminate\Support\Facades\DB;

/**
 * Prepara los datasets de Chart.js para los 7 gráficos del dashboard.
 *
 * Todos los métodos retornan un array compatible con Chart.js 4:
 * ['labels' => [...], 'datasets' => [...]]
 *
 * SUPUESTOS:
 * - Aging usa risk_level como proxy de bucket de edad (no hay columnas bucket_x).
 * - Tendencia usa period_date de portfolio_documents (cortes de cartera cargados).
 * - Pareto ordena por pending_amount desc, Top 10.
 */
class ChartService
{
    private const RISK_COLORS = [
        'normal'   => '#10b981',
        'low'      => '#3b82f6',
        'medium'   => '#f59e0b',
        'high'     => '#f97316',
        'critical' => '#ef4444',
    ];

    private const RISK_LABELS = [
        'normal'   => 'Normal (0-30d)',
        'low'      => 'Bajo (31-60d)',
        'medium'   => 'Medio (61-90d)',
        'high'     => 'Alto (91-180d)',
        'critical' => 'Crítico (181+d)',
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
        $rows = $this->baseActiveQuery($filters)
            ->select('pd.risk_level', DB::raw('SUM(pd.pending_amount) as total'))
            ->groupBy('pd.risk_level')
            ->get()
            ->keyBy('risk_level');

        $keys   = ['normal', 'low', 'medium', 'high', 'critical'];
        $totals = array_map(fn ($k) => (float) ($rows[$k]->total ?? 0), $keys);
        $grand  = array_sum($totals);

        $labels   = array_map(fn ($k) => self::RISK_LABELS[$k], $keys);
        $pcts     = array_map(fn ($v) => $grand > 0 ? round($v / $grand * 100, 1) : 0, $totals);
        $colors   = array_map(fn ($k) => self::RISK_COLORS[$k], $keys);

        return [
            'labels'   => $labels,
            'datasets' => [
                [
                    'label'           => 'Saldo Pendiente',
                    'data'            => $totals,
                    'backgroundColor' => $colors,
                    'borderColor'     => '#ffffff',
                    'borderWidth'     => 2,
                ],
            ],
            'pcts' => $pcts,  // para tooltips personalizados
        ];
    }

    // ── 2. Tendencia de exposición por período ────────────────────────────

    public function trend(DashboardFiltersData $filters): array
    {
        // Agrupa por mes de issue_date — muestra todos los meses disponibles
        // Si hay períodos seleccionados los usa como límite; si no, muestra los últimos 24 meses
        $q = DB::table('portfolio_documents as pd')
            ->join('portfolio_loads as pl', 'pl.id', '=', 'pd.portfolio_load_id')
            ->join('clients as c', 'c.id', '=', 'pd.client_id')
            ->leftJoin('advisors as a', 'a.id', '=', 'pd.advisor_id')
            ->where('pl.is_active', true)
            ->where('pl.status', 'completed')
            ->whereNull('pd.deleted_at')
            ->whereIn('pd.status', ['active', 'partial', 'in_process'])
            ->whereNotNull('pd.issue_date')
            ->select(
                DB::raw("LEFT(pd.issue_date, 7) as ym"),
                DB::raw('SUM(pd.pending_amount) as total')
            )
            ->groupBy('ym')
            ->orderBy('ym');

        // Si hay períodos seleccionados, filtrar solo esos
        if (!empty($filters->periods)) {
            $q->whereIn(DB::raw("LEFT(pd.issue_date, 7)"), $filters->periods);
        } else {
            $q->limit(24);
        }

        // Aplica filtros de dimensión (uen, canal, regional, asesor, cliente)
        $this->applyDimensionFilters($q, $filters);

        $rows = $q->get();

        $yms    = $rows->pluck('ym')->toArray();
        $labels = array_map(fn ($ym) => \Carbon\Carbon::parse($ym . '-01')->format('M Y'), $yms);
        $totals = $rows->map(fn ($r) => (float) $r->total)->toArray();
        $critical = $this->criticalTrend($filters, $yms);

        return [
            'labels'   => $labels,
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

        $q = DB::table('portfolio_documents as pd')
            ->join('portfolio_loads as pl', 'pl.id', '=', 'pd.portfolio_load_id')
            ->join('clients as c', 'c.id', '=', 'pd.client_id')
            ->leftJoin('advisors as a', 'a.id', '=', 'pd.advisor_id')
            ->where('pl.is_active', true)
            ->where('pl.status', 'completed')
            ->whereNull('pd.deleted_at')
            ->whereIn('pd.status', ['active', 'partial', 'in_process'])
            ->whereIn(DB::raw("LEFT(pd.issue_date, 7)"), $yms)
            ->where('pd.days_overdue', '>', 90)
            ->select(
                DB::raw("LEFT(pd.issue_date, 7) as ym"),
                DB::raw('SUM(pd.pending_amount) as total')
            )
            ->groupBy('ym');

        $this->applyDimensionFilters($q, $filters);

        $rows = $q->get()->keyBy('ym');

        return array_map(fn ($ym) => (float) ($rows[$ym]->total ?? 0), $yms);
    }

    // ── 3/4. Cartera vencida por dimensión (UEN / Canal) ──────────────────

    public function byDimension(DashboardFiltersData $filters, string $column, string $label): array
    {
        $rows = $this->baseActiveQuery($filters)
            ->select(DB::raw("{$column} as dim_value"), DB::raw('SUM(pd.pending_amount) as total'))
            ->where('pd.days_overdue', '>', 0)
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->groupBy($column)
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $labels = $rows->pluck('dim_value')->map(fn ($v) => $v ?: 'Sin ' . $label)->toArray();
        $totals = $rows->pluck('total')->map(fn ($v) => (float) $v)->toArray();

        return [
            'labels'   => $labels,
            'datasets' => [[
                'label'           => "Cartera Vencida por {$label}",
                'data'            => $totals,
                'backgroundColor' => '#f97316',
                'borderColor'     => '#ea580c',
                'borderWidth'     => 1,
                'borderRadius'    => 4,
            ]],
        ];
    }

    // ── 5. Cartera vencida por asesor ─────────────────────────────────────

    public function byAdvisor(DashboardFiltersData $filters): array
    {
        $rows = $this->baseActiveQuery($filters)
            ->select('a.name as advisor_name', DB::raw('SUM(pd.pending_amount) as total'))
            ->where('pd.days_overdue', '>', 0)
            ->whereNotNull('a.name')
            ->groupBy('a.name')
            ->orderByDesc('total')
            ->limit(12)
            ->get();

        $labels = $rows->pluck('advisor_name')->map(fn ($v) => $v ?: 'Sin Asesor')->toArray();
        $totals = $rows->pluck('total')->map(fn ($v) => (float) $v)->toArray();

        return [
            'labels'   => $labels,
            'datasets' => [[
                'label'           => 'Cartera Vencida por Asesor',
                'data'            => $totals,
                'backgroundColor' => '#8b5cf6',
                'borderColor'     => '#7c3aed',
                'borderWidth'     => 1,
                'borderRadius'    => 4,
            ]],
        ];
    }

    // ── 6. Pareto de clientes (Top 10) ────────────────────────────────────

    public function pareto(DashboardFiltersData $filters): array
    {
        $rows = $this->baseActiveQuery($filters)
            ->select('c.name as client_name', DB::raw('SUM(pd.pending_amount) as total'))
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

        return [
            'labels'   => $labels,
            'datasets' => [
                [
                    'type'            => 'bar',
                    'label'           => 'Saldo Pendiente',
                    'data'            => $totals,
                    'backgroundColor' => '#2563eb',
                    'borderColor'     => '#1d4ed8',
                    'borderWidth'     => 1,
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
            ->where('pl.is_active', true)
            ->where('pl.status', 'completed')
            ->whereIn('pd.status', ['active', 'partial', 'in_process'])
            ->whereNull('pd.deleted_at');

        // Período — filtra por issue_date (fecha de emisión del documento)
        if (!empty($filters->periods)) {
            $q->whereIn(DB::raw("LEFT(pd.issue_date, 7)"), $filters->periods);
        } elseif ($filters->period) {
            $ym = substr($filters->period, 0, 7);
            $q->whereRaw("LEFT(pd.issue_date, 7) = ?", [$ym]);
        } elseif ($filters->dateFrom && $filters->dateTo) {
            $q->whereBetween('pd.issue_date', [$filters->dateFrom, $filters->dateTo]);
        }
        // Sin período → muestra todos los documentos activos

        $this->applyDimensionFilters($q, $filters);

        return $q;
    }

    private function applyDimensionFilters($q, DashboardFiltersData $filters): void
    {
        if (!empty($filters->uens))       $q->whereIn('c.uen', $filters->uens);
        elseif ($filters->uen)            $q->where('c.uen', $filters->uen);

        if (!empty($filters->regionals))  $q->whereIn('c.region', $filters->regionals);
        elseif ($filters->regional)       $q->where('c.region', $filters->regional);

        if (!empty($filters->channels))   $q->whereIn('c.channel', $filters->channels);
        elseif ($filters->channel)        $q->where('c.channel', $filters->channel);

        if (!empty($filters->advisors))   $q->whereIn('pd.advisor_id', $filters->advisors);
        elseif ($filters->advisorId)      $q->where('pd.advisor_id', $filters->advisorId);

        if ($filters->clientId)           $q->where('pd.client_id', $filters->clientId);
    }
}
