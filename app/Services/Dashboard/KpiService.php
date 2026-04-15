<?php

namespace App\Services\Dashboard;

use App\Data\DashboardFiltersData;
use Illuminate\Support\Facades\DB;

/**
 * Calcula los 13 KPIs del dashboard estratégico.
 *
 * SUPUESTOS DOCUMENTADOS:
 * ─────────────────────────────────────────────────────────────────────────
 * KPI 3 - ISM: Ponderación de buckets de mora alta.
 *   Formula: (SUM(high)*0.5 + SUM(critical)*1.0) / cartera_total * 100
 *   Donde: high = risk_level 'high' (91-180d), critical = risk_level 'critical' (181+d)
 *   Razón: los buckets > 90d tienen distinto peso de riesgo real para la cartera.
 *   Parametrizable vía config('dashboard.ism_weights').
 *
 * KPI 4 - Rotación: (cartera_total / recaudo_período) * 30
 *   Representa cuántos días tarda en rotar la cartera a ritmo de recaudo actual.
 *   Si recaudo = 0, retorna null (estado vacío, no cero).
 *
 * KPI 8 - Recaudo del período: SUM(collection_details.amount) para el período.
 *   Período = payment_date en el rango derivado del filtro activo.
 *
 * KPI 10/11 - Presupuesto: si no existe fila en budget_collections, retorna null.
 *   El período de presupuesto se resuelve como el primer día del mes del filtro.
 *
 * KPI 12 - % Saldo Negativo: documentos con pending_amount < 0 indican créditos
 *   o anticipos aplicados que generan saldo a favor. Incluidos en el total para
 *   reflejar la cartera neta real.
 *
 * Score de Salud:
 *   score = (1 - clamp(critical_rate/30)) * 30
 *         + (1 - clamp(ism/50))          * 20
 *         + clamp(recovery_rate/15)      * 25
 *         + (1 - clamp(conc_top5/70))   * 15
 *         + (1 - clamp(neg_rate/5))      * 10
 *   Rango: 0-100. Verde ≥ 80, Amarillo 60-79, Rojo < 60.
 * ─────────────────────────────────────────────────────────────────────────
 */
class KpiService
{
    public function compute(DashboardFiltersData $filters): array
    {
        // ── Cartera activa (status activo/parcial/en proceso) ──────────────
        $activeBase = $this->activePortfolioQuery($filters);

        $portfolioTotal   = (clone $activeBase)->sum('pd.pending_amount');
        $portfolioTotal   = (float) $portfolioTotal;

        // ── KPI 1: Cartera Total ───────────────────────────────────────────
        // ── KPI 2: % Cartera Crítica (>90 días) y % Cartera Vencida ───────
        $criticalAmount = (float) (clone $activeBase)
            ->where('pd.days_overdue', '>', 90)
            ->sum('pd.pending_amount');

        $overdueAmount = (float) (clone $activeBase)
            ->where('pd.days_overdue', '>', 0)
            ->sum('pd.pending_amount');

        $criticalRate = $portfolioTotal > 0
            ? round($criticalAmount / $portfolioTotal * 100, 2)
            : 0.0;

        $overdueRate = $portfolioTotal > 0
            ? round($overdueAmount / $portfolioTotal * 100, 2)
            : 0.0;

        // ── KPI 3: Índice de Severidad de Mora (ratio, idéntico a mcmdef) ────
        // Pesos exactos: 31-60d × 0.5, 61-90d × 1.0, 91-180d × 2.0,
        //   181-360d × 3.0, 361+d × 4.0  (5 buckets usando days_overdue)
        $ismB31_60   = (float) (clone $activeBase)->whereBetween('pd.days_overdue', [31,  60])->sum('pd.pending_amount');
        $ismB61_90   = (float) (clone $activeBase)->whereBetween('pd.days_overdue', [61,  90])->sum('pd.pending_amount');
        $ismB91_180  = (float) (clone $activeBase)->whereBetween('pd.days_overdue', [91, 180])->sum('pd.pending_amount');
        $ismB181_360 = (float) (clone $activeBase)->whereBetween('pd.days_overdue', [181, 360])->sum('pd.pending_amount');
        $ismB361plus = (float) (clone $activeBase)->where('pd.days_overdue', '>', 360)->sum('pd.pending_amount');
        $ismValue    = $portfolioTotal > 0
            ? round(
                ($ismB31_60 * 0.5 + $ismB61_90 * 1.0 + $ismB91_180 * 2.0
                    + $ismB181_360 * 3.0 + $ismB361plus * 4.0)
                / $portfolioTotal,
                2
            )
            : 0.0;

        // ── KPI 7: % Documentos Vencidos ─────────────────────────────────
        $totalDocs   = (clone $activeBase)->count('pd.id');
        $overdueDocs = (clone $activeBase)->where('pd.days_overdue', '>', 0)->count('pd.id');
        $overdueDocRate = $totalDocs > 0 ? round($overdueDocs / $totalDocs * 100, 2) : 0.0;

        // ── KPI 8: Recaudo del período ────────────────────────────────────
        // Solo consulta recaudo si hay un período activo (igual que mcmdef)
        $periodSelected = !empty($filters->periods)
            || !empty($filters->period)
            || (!empty($filters->dateFrom) && !empty($filters->dateTo));

        $recaudoPeriod  = $periodSelected ? $this->collectionForPeriod($filters) : 0.0;
        $hasRecaudoData = $periodSelected && $recaudoPeriod > 0;

        // ── KPI 4: Rotación de Cartera ────────────────────────────────────
        $rotation = ($hasRecaudoData && $portfolioTotal > 0)
            ? round(($portfolioTotal / $recaudoPeriod) * 30, 1)
            : null;

        // ── KPI 9: % Recuperación del período ────────────────────────────
        $recoveryRate = ($hasRecaudoData && $portfolioTotal > 0)
            ? round($recaudoPeriod / $portfolioTotal * 100, 2)
            : null;

        // ── KPI 5: % Concentración Top 5 Clientes ────────────────────────
        $top5Amount = $this->topClientsAmount($filters, 5);
        $concTop5   = $portfolioTotal > 0 ? round($top5Amount / $portfolioTotal * 100, 2) : 0.0;

        // ── KPI 6 + 13: Dependencia del Cliente Mayor ─────────────────────
        $topClient       = $this->topClient($filters);
        $topClientAmount = (float) ($topClient?->total ?? 0);
        $topClientRate   = $portfolioTotal > 0
            ? round($topClientAmount / $portfolioTotal * 100, 2)
            : 0.0;

        // ── KPI 10: Presupuesto del período ──────────────────────────────
        $budget = $this->budgetForPeriod($filters);

        // ── KPI 11: Recaudo vs Meta ───────────────────────────────────────
        $vsMetaRate = ($hasRecaudoData && $budget !== null && $budget > 0)
            ? round($recaudoPeriod / $budget * 100, 2)
            : null;

        // ── KPI 12: % Saldo Negativo (null = sin recaudo, igual que mcmdef) ──
        $negativeAmount = (float) (clone $activeBase)
            ->where('pd.pending_amount', '<', 0)
            ->sum('pd.pending_amount');
        $negativeRate = ($hasRecaudoData && $portfolioTotal != 0 && $negativeAmount != 0)
            ? round(abs($negativeAmount) / abs($portfolioTotal) * 100, 2)
            : null;

        // ── Score de Salud (fórmula mcmdef) ──────────────────────────────
        // score = 100 - (ism*15 + conc_top5*0.30 + critical_rate*0.30 + overdue_doc_rate*0.25)
        $score = $this->computeScore(
            ism:            $ismValue,
            concTop5:       $concTop5,
            criticalRate:   $criticalRate,
            overdueDocRate: $overdueDocRate,
        );

        return [
            'portfolio_total'   => $portfolioTotal,
            'critical_amount'   => $criticalAmount,
            'critical_rate'     => $criticalRate,
            'overdue_amount'    => $overdueAmount,
            'overdue_rate'      => $overdueRate,
            'ism'               => $ismValue,
            'rotation'          => $rotation,
            'conc_top5'         => $concTop5,
            'top_client'        => [
                'name'   => $topClient?->client_name ?? null,
                'amount' => $topClientAmount,
                'rate'   => $topClientRate,
            ],
            'overdue_doc_rate'  => $overdueDocRate,
            'total_docs'        => $totalDocs,
            'overdue_docs'      => $overdueDocs,
            'recaudo_period'    => $recaudoPeriod,
            'recovery_rate'     => $recoveryRate,
            'budget'            => $budget,
            'vs_meta_rate'      => $vsMetaRate,
            'negative_rate'     => $negativeRate,
            'negative_amount'   => $negativeAmount,
            'score'             => $score,
            'score_label'       => $this->scoreLabel($score),
            'score_color'       => $this->scoreColor($score),
            'score_drivers'     => $this->scoreDrivers($score, $criticalRate, $ismValue, $concTop5, $overdueDocRate),
            'portfolio_empty'   => $portfolioTotal == 0,
            'has_recaudo_data'  => $hasRecaudoData,
            'period_selected'   => $periodSelected,
        ];
    }

    // ── Base query builder ─────────────────────────────────────────────────

    /**
     * Query base sobre portfolio_documents con JOINs de clients y advisors
     * y filtros de dimensión aplicados.
     */
    private function activePortfolioQuery(DashboardFiltersData $filters)
    {
        $q = DB::table('portfolio_documents as pd')
            ->join('portfolio_loads as pl', 'pl.id', '=', 'pd.portfolio_load_id')
            ->join('clients as c', 'c.id', '=', 'pd.client_id')
            ->leftJoin('advisors as a', 'a.id', '=', 'pd.advisor_id')
            ->where('pl.is_active', true)
            ->where('pl.status', 'completed')
            ->whereIn('pd.status', ['active', 'partial', 'in_process'])
            ->whereNull('pd.deleted_at');

        $this->applyPortfolioFilters($q, $filters);

        return $q;
    }

    private function baseQuery(DashboardFiltersData $filters)
    {
        $q = DB::table('portfolio_documents as pd')
            ->join('portfolio_loads as pl', 'pl.id', '=', 'pd.portfolio_load_id')
            ->join('clients as c', 'c.id', '=', 'pd.client_id')
            ->leftJoin('advisors as a', 'a.id', '=', 'pd.advisor_id')
            ->where('pl.is_active', true)
            ->where('pl.status', 'completed')
            ->whereNull('pd.deleted_at');

        $this->applyPortfolioFilters($q, $filters);

        return $q;
    }

    private function applyPortfolioFilters($query, DashboardFiltersData $filters): void
    {
        // Período — filtra por issue_date (fecha de emisión del documento, como mcmdef)
        if (!empty($filters->periods)) {
            $query->whereIn(DB::raw("LEFT(pd.issue_date, 7)"), $filters->periods);
        } elseif ($filters->period) {
            $ym = substr($filters->period, 0, 7);
            $query->whereRaw("LEFT(pd.issue_date, 7) = ?", [$ym]);
        } elseif ($filters->dateFrom && $filters->dateTo) {
            $query->whereBetween('pd.issue_date', [$filters->dateFrom, $filters->dateTo]);
        }
        // Sin filtro de período → muestra todos los documentos activos

        // UEN
        if (!empty($filters->uens)) {
            $query->whereIn('c.uen', $filters->uens);
        } elseif ($filters->uen) {
            $query->where('c.uen', $filters->uen);
        }

        // Regional
        if (!empty($filters->regionals)) {
            $query->whereIn('c.region', $filters->regionals);
        } elseif ($filters->regional) {
            $query->where('c.region', $filters->regional);
        }

        // Canal
        if (!empty($filters->channels)) {
            $query->whereIn('c.channel', $filters->channels);
        } elseif ($filters->channel) {
            $query->where('c.channel', $filters->channel);
        }

        // Asesor — multi-select tiene prioridad
        if (!empty($filters->advisors)) {
            $query->whereIn('pd.advisor_id', $filters->advisors);
        } elseif ($filters->advisorId) {
            $query->where('pd.advisor_id', $filters->advisorId);
        }

        if ($filters->clientId) {
            $query->where('pd.client_id', $filters->clientId);
        }
    }

    // ── Recaudo ────────────────────────────────────────────────────────────

    private function collectionForPeriod(DashboardFiltersData $filters): float
    {
        $q = DB::table('collection_details as cd')
            ->join('collection_loads as cl', 'cl.id', '=', 'cd.collection_load_id')
            ->join('clients as c', 'c.id', '=', 'cd.client_id');

        $q->where('cl.is_active', true)
            ->where('cl.status', 'completed');

        // Rango de fechas de pago
        if (!empty($filters->periods)) {
            $q->where(function ($sub) use ($filters) {
                foreach ($filters->periods as $ym) {
                    $sub->orWhereRaw("DATE_FORMAT(cd.payment_date, '%Y-%m') = ?", [$ym]);
                }
            });
        } elseif ($filters->period) {
            $start = \Carbon\Carbon::parse($filters->period . '-01')->startOfMonth();
            $end   = \Carbon\Carbon::parse($filters->period . '-01')->endOfMonth();
            $q->whereBetween('cd.payment_date', [$start->toDateString(), $end->toDateString()]);
        } elseif ($filters->dateFrom && $filters->dateTo) {
            $q->whereBetween('cd.payment_date', [$filters->dateFrom, $filters->dateTo]);
        }

        // Filtros de dimensión aplicables a collection_details
        if ($filters->clientId) {
            $q->where('cd.client_id', $filters->clientId);
        }
        if ($filters->regional) {
            $q->where(function ($sub) use ($filters) {
                $sub->where('cd.regional', $filters->regional)
                    ->orWhere('c.region', $filters->regional);
            });
        }
        if ($filters->channel) {
            $q->where(function ($sub) use ($filters) {
                $sub->where('cd.channel', $filters->channel)
                    ->orWhere('c.channel', $filters->channel);
            });
        }
        if ($filters->uen) {
            $q->where('c.uen', $filters->uen);
        }

        return (float) $q->sum('cd.amount');
    }

    // ── Concentración ─────────────────────────────────────────────────────

    private function topClientsAmount(DashboardFiltersData $filters, int $n): float
    {
        $sub = $this->activePortfolioQuery($filters)
            ->select('pd.client_id', DB::raw('SUM(pd.pending_amount) as total'))
            ->groupBy('pd.client_id')
            ->orderByDesc('total')
            ->limit($n);

        return (float) DB::table(DB::raw("({$sub->toSql()}) as top"))
            ->mergeBindings($sub)
            ->sum('total');
    }

    private function topClient(DashboardFiltersData $filters): ?object
    {
        return $this->activePortfolioQuery($filters)
            ->select('c.name as client_name', DB::raw('SUM(pd.pending_amount) as total'))
            ->groupBy('pd.client_id', 'c.name')
            ->orderByDesc('total')
            ->first();
    }

    // ── Presupuesto ───────────────────────────────────────────────────────

    private function budgetForPeriod(DashboardFiltersData $filters): ?float
    {
        $periodDate = $filters->budgetPeriodDate();
        if (!$periodDate) {
            $latest = DB::table('portfolio_documents')->max('period_date');
            $periodDate = $latest
                ? \Carbon\Carbon::parse($latest)->startOfMonth()->toDateString()
                : null;
        }

        if (!$periodDate) {
            return null;
        }

        $q = DB::table('budget_collections')
            ->whereYear('period_date', \Carbon\Carbon::parse($periodDate)->year)
            ->whereMonth('period_date', \Carbon\Carbon::parse($periodDate)->month);

        if ($filters->uen) {
            $q->where('uen', $filters->uen);
        }
        if ($filters->regional) {
            $q->where('regional', $filters->regional);
        }
        if ($filters->channel) {
            $q->where('channel', $filters->channel);
        }

        $total = $q->sum('amount');
        return $total > 0 ? (float) $total : null;
    }

    // ── Score ─────────────────────────────────────────────────────────────

    private function computeScore(
        float $ism,
        float $concTop5,
        float $criticalRate,
        float $overdueDocRate,
    ): int {
        // Fórmula mcmdef: 100 - penalización
        // ism ya es un ratio (0.2), concTop5 y criticalRate son porcentajes
        $penalty = ($ism * 15)
                 + ($concTop5 * 0.30)
                 + ($criticalRate * 0.30)
                 + ($overdueDocRate * 0.25);

        return (int) round(min(max(100 - $penalty, 0), 100));
    }

    private function scoreLabel(int $score): string
    {
        return match(true) {
            $score >= 80 => 'Saludable',
            $score >= 60 => 'Riesgo Medio',
            default      => 'Deterioro Alto',
        };
    }

    private function scoreColor(int $score): string
    {
        return match(true) {
            $score >= 80 => 'green',
            $score >= 60 => 'amber',
            default      => 'red',
        };
    }

    private function scoreDrivers(int $score, float $cr, float $ism, float $c5, float $ovDocRate): array
    {
        if ($score >= 80) {
            $drivers = [];
            if ($cr < 10)       $drivers[] = ['icon' => '✓', 'text' => 'Mora crítica controlada (' . $cr . '%)'];
            if ($ism < 0.5)     $drivers[] = ['icon' => '✓', 'text' => 'Severidad de mora baja (ISM ' . $ism . ')'];
            if ($c5 < 50)       $drivers[] = ['icon' => '✓', 'text' => 'Cartera diversificada'];
            if ($ovDocRate < 30) $drivers[] = ['icon' => '✓', 'text' => 'Bajo % de documentos vencidos'];
            return $drivers ?: [['icon' => '✓', 'text' => 'Indicadores dentro del rango saludable']];
        }

        $risks = [];
        if ($cr > 20)          $risks[] = ['icon' => '⚠', 'text' => 'Mora crítica elevada (' . $cr . '%)'];
        if ($ism > 1.6)        $risks[] = ['icon' => '⚠', 'text' => 'Severidad de mora alta (ISM ' . $ism . ')'];
        if ($c5 > 60)          $risks[] = ['icon' => '⚠', 'text' => 'Alta concentración en Top 5 (' . $c5 . '%)'];
        if ($ovDocRate > 50)   $risks[] = ['icon' => '⚠', 'text' => 'Alto % de documentos vencidos (' . $ovDocRate . '%)'];
        return $risks ?: [['icon' => '⚠', 'text' => 'Revisar indicadores de mora y concentración']];
    }
}
