<?php

namespace App\Services\Dashboard;

use App\Data\DashboardFiltersData;
use App\Services\Dashboard\Concerns\AppliesCollectionPaymentDateScope;
use App\Services\Dashboard\Concerns\AppliesLatestCollectionLoad;
use App\Services\Dashboard\Concerns\AppliesOperativePortfolioDocuments;
use App\Services\Dashboard\Concerns\AppliesPortfolioPeriodCut;
use App\Services\Risk\Concerns\AppliesLiveDaysOverdue;
use Illuminate\Support\Facades\DB;

/**
 * Calcula los 13 KPIs del dashboard estratégico.
 *
 * SUPUESTOS DOCUMENTADOS:
 * ─────────────────────────────────────────────────────────────────────────
 * Cartera total: SUM(pending_amount) de la carga activa más reciente (un periodo),
 *   documentos operativos, corte period_date de esa carga; opcional filtro issue_date.
 *
 * KPI 3 - ISM: buckets por días de mora vivos (vencimiento → fecha de consulta) ponderados / cartera_total.
 *
 * KPI 4 - Rotación: (cartera_total / recaudo_período) * 30
 *   Representa cuántos días tarda en rotar la cartera a ritmo de recaudo actual.
 *   Si recaudo = 0, retorna null (estado vacío, no cero).
 *
 * KPI 8 - Recaudo: última carga de recaudo activa, pagos del corte del mes de la
 *   cartera activa (independiente del filtro Desde/Hasta del tablero).
 * KPI 9 - % Recuperación: recaudo del corte del mes / cartera del mismo corte.
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
    use AppliesCollectionPaymentDateScope;
    use AppliesLatestCollectionLoad;
    use AppliesLiveDaysOverdue;
    use AppliesOperativePortfolioDocuments;
    use AppliesPortfolioPeriodCut;
    public function compute(DashboardFiltersData $filters): array
    {
        // ── Cartera: último corte + filtro opcional por fecha de contabilización ──
        $activeBase = $this->activePortfolioQuery($filters);

        $balanceBase = clone $activeBase;
        $this->applyPortfolioBalanceStatus($balanceBase);
        $portfolioTotal = (float) (clone $balanceBase)->sum('pd.pending_amount');

        $operativeBase = clone $activeBase;
        $this->applyOperativeDocumentStatus($operativeBase);

        // ── KPI 1: Cartera Total (misma base que total_pending_amount de la carga) ──
        // ── KPI 2: % Cartera Crítica (>90 días) y % Cartera Vencida (solo operativos) ──
        $criticalBase = clone $operativeBase;
        $this->whereLiveDaysOverdue($criticalBase, '>', 90, $filters);
        $criticalAmount = (float) $criticalBase->sum('pd.pending_amount');

        $overdueBase = clone $operativeBase;
        $this->whereLiveDaysOverdue($overdueBase, '>', 0, $filters);
        $overdueAmount = (float) $overdueBase->sum('pd.pending_amount');

        $criticalRate = $portfolioTotal > 0
            ? round($criticalAmount / $portfolioTotal * 100, 2)
            : 0.0;

        $overdueRate = $portfolioTotal > 0
            ? round($overdueAmount / $portfolioTotal * 100, 2)
            : 0.0;

        // ── KPI 3: Índice de Severidad de Mora (ratio, idéntico a mcmdef) ────
        // Pesos exactos: 31-60d × 0.5, 61-90d × 1.0, 91-180d × 2.0,
        //   181-360d × 3.0, 361+d × 4.0  (5 buckets usando days_overdue)
        $ismB31_60   = (float) $this->ismBucketSum($operativeBase, $filters, 31, 60);
        $ismB61_90   = (float) $this->ismBucketSum($operativeBase, $filters, 61, 90);
        $ismB91_180  = (float) $this->ismBucketSum($operativeBase, $filters, 91, 180);
        $ismB181_360 = (float) $this->ismBucketSum($operativeBase, $filters, 181, 360);
        $ismB361plus = (float) $this->ismBucketSum($operativeBase, $filters, 361, 99999);
        $ismValue    = $portfolioTotal > 0
            ? round(
                ($ismB31_60 * 0.5 + $ismB61_90 * 1.0 + $ismB91_180 * 2.0
                    + $ismB181_360 * 3.0 + $ismB361plus * 4.0)
                / $portfolioTotal,
                2
            )
            : 0.0;

        // ── KPI 7: % Documentos Vencidos ─────────────────────────────────
        $totalDocs   = (clone $balanceBase)->count('pd.id');
        $overdueDocsBase = clone $operativeBase;
        $this->whereLiveDaysOverdue($overdueDocsBase, '>', 0, $filters);
        $overdueDocs = $overdueDocsBase->count('pd.id');
        $overdueDocRate = $totalDocs > 0 ? round($overdueDocs / $totalDocs * 100, 2) : 0.0;

        // ── KPI 8/9: Recaudo y recuperación (corte fijo del mes de cartera activa) ──
        $collectionLoad   = $this->resolveDashboardCollectionLoad();
        $hasRecaudoLoad   = $collectionLoad !== null;
        $recaudoFilters   = $this->filtersForRecaudoMonthCut($filters);
        $recaudoPeriod    = $this->collectionForPeriod($recaudoFilters);
        $hasRecaudoData   = $hasRecaudoLoad && $recaudoPeriod > 0;

        $portfolioMonthBase = $this->activePortfolioQuery($recaudoFilters);
        $this->applyPortfolioBalanceStatus($portfolioMonthBase);
        $portfolioForRecaudo = (float) (clone $portfolioMonthBase)->sum('pd.pending_amount');

        $periodSelected = ! empty($filters->period)
            || ! empty($filters->dateFrom)
            || ! empty($filters->dateTo);

        // ── KPI 4: Rotación (mismo corte de mes que recaudo) ───────────────
        $rotation = ($hasRecaudoData && $portfolioForRecaudo > 0)
            ? round(($portfolioForRecaudo / $recaudoPeriod) * 30, 1)
            : null;

        // ── KPI 9: % Recuperación del corte del mes ───────────────────────
        $recoveryRate = ($hasRecaudoData && $portfolioForRecaudo > 0)
            ? round($recaudoPeriod / $portfolioForRecaudo * 100, 2)
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

        $negativeByDocumentType = [];
        if ($negativeAmount != 0.0) {
            $negAbs = abs($negativeAmount);
            $rows = (clone $activeBase)
                ->where('pd.pending_amount', '<', 0)
                ->select('pd.document_type', DB::raw('SUM(pd.pending_amount) as total'))
                ->groupBy('pd.document_type')
                ->orderByRaw('SUM(pd.pending_amount) ASC')
                ->get();
            foreach ($rows as $row) {
                $type = (string) ($row->document_type ?? '');
                $type = $type !== '' ? $type : 'N/D';
                $amt = (float) $row->total;
                $negativeByDocumentType[] = [
                    'document_type' => $type,
                    'amount'        => $amt,
                    'share_pct'     => $negAbs > 0 ? round(abs($amt) / $negAbs * 100, 1) : 0.0,
                ];
            }
        }

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
            'collection_load_id' => $collectionLoad?->id,
            'collection_load_ref' => $collectionLoad?->reference,
            'collection_load_total' => $collectionLoad ? (float) ($collectionLoad->total_collected ?? 0) : 0.0,
            'collection_date_label' => $this->defaultMonthCutPaymentLabel(),
            'has_recaudo_load'  => $hasRecaudoLoad,
            'budget'            => $budget,
            'vs_meta_rate'      => $vsMetaRate,
            'negative_rate'     => $negativeRate,
            'negative_amount'   => $negativeAmount,
            'negative_by_document_type' => $negativeByDocumentType,
            'score'             => $score,
            'score_label'       => $this->scoreLabel($score),
            'score_color'       => $this->scoreColor($score),
            'score_drivers'     => $this->scoreDrivers($score, $criticalRate, $ismValue, $concTop5, $overdueDocRate),
            'portfolio_empty'   => $portfolioTotal == 0,
            'has_recaudo_data'  => $hasRecaudoData,
            'period_selected'   => $periodSelected,
        ];
    }

    private function ismBucketSum($activeBase, DashboardFiltersData $filters, int $min, int $max): float
    {
        $q = clone $activeBase;
        if ($max >= 99999) {
            $this->whereLiveDaysOverdue($q, '>', $min, $filters);
        } else {
            $this->whereLiveDaysBetween($q, $min, $max, $filters);
        }

        return (float) $q->sum('pd.pending_amount');
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
            ->where('pl.status', 'completed')
            ->whereNull('pd.deleted_at');
        $this->applyDashboardPortfolioLoad($q);

        $this->applyPortfolioFilters($q, $filters);

        return $q;
    }

    private function baseQuery(DashboardFiltersData $filters)
    {
        $q = DB::table('portfolio_documents as pd')
            ->join('portfolio_loads as pl', 'pl.id', '=', 'pd.portfolio_load_id')
            ->join('clients as c', 'c.id', '=', 'pd.client_id')
            ->leftJoin('advisors as a', 'a.id', '=', 'pd.advisor_id')
            ->where('pl.status', 'completed')
            ->whereNull('pd.deleted_at');
        $this->applyDashboardPortfolioLoad($q);

        $this->applyPortfolioFilters($q, $filters);

        return $q;
    }

    private function applyPortfolioFilters($query, DashboardFiltersData $filters): void
    {
        $this->applyPortfolioPeriodCut($query, $filters);

        // UEN
        if (!empty($filters->uens)) {
            $vals = array_values(array_unique(array_map(static fn ($v) => trim((string) $v), $filters->uens)));
            $vals = array_values(array_filter($vals, static fn ($v) => $v !== ''));
            if ($vals !== []) {
                $query->whereIn(DB::raw('TRIM(c.uen)'), $vals);
            }
        } elseif ($filters->uen) {
            $query->whereRaw('TRIM(c.uen) = ?', [trim($filters->uen)]);
        }

        // Regional
        if (!empty($filters->regionals)) {
            $vals = array_values(array_unique(array_map(static fn ($v) => trim((string) $v), $filters->regionals)));
            $vals = array_values(array_filter($vals, static fn ($v) => $v !== ''));
            if ($vals !== []) {
                $query->whereIn(DB::raw('TRIM(c.region)'), $vals);
            }
        } elseif ($filters->regional) {
            $query->whereRaw('TRIM(c.region) = ?', [trim($filters->regional)]);
        }

        // Canal
        if (!empty($filters->channels)) {
            $vals = array_values(array_unique(array_map(static fn ($v) => trim((string) $v), $filters->channels)));
            $vals = array_values(array_filter($vals, static fn ($v) => $v !== ''));
            if ($vals !== []) {
                $query->whereIn(DB::raw('TRIM(c.channel)'), $vals);
            }
        } elseif ($filters->channel) {
            $query->whereRaw('TRIM(c.channel) = ?', [trim($filters->channel)]);
        }

        // Asesor — multi-select (IDs expandidos por nombre vía DashboardFilterCascadeService)
        app(DashboardFilterCascadeService::class)->applyPortfolioAdvisorConstraint($query, $filters);

        if ($filters->clientId) {
            $query->where('pd.client_id', $filters->clientId);
        }

        if (!empty($filters->riskLevels)) {
            $query->whereIn('pd.risk_level', $filters->riskLevels);
        }

        if (!empty($filters->documentTypes)) {
            $query->whereIn('pd.document_type', $filters->documentTypes);
        }
    }

    /**
     * Filtros para recaudo/recuperación: dimensiones del tablero + corte del mes de cartera.
     */
    private function filtersForRecaudoMonthCut(DashboardFiltersData $filters): DashboardFiltersData
    {
        [$start, $end] = $this->resolveDefaultMonthCutRange();

        return new DashboardFiltersData(
            period: null,
            uen: $filters->uen,
            regional: $filters->regional,
            channel: $filters->channel,
            advisorId: $filters->advisorId,
            clientId: $filters->clientId,
            dateFrom: $start,
            dateTo: $end,
            channels: $filters->channels,
            uens: $filters->uens,
            regionals: $filters->regionals,
            advisors: $filters->advisors,
            riskLevels: $filters->riskLevels,
            documentTypes: $filters->documentTypes,
        );
    }

    // ── Recaudo ────────────────────────────────────────────────────────────

    private function collectionForPeriod(DashboardFiltersData $filters): float
    {
        $load = $this->resolveDashboardCollectionLoad();
        if ($load === null) {
            return 0.0;
        }

        $q = DB::table('collection_details as cd')
            ->leftJoin('clients as c', 'c.id', '=', 'cd.client_id')
            ->where('cd.collection_load_id', (int) $load->id);

        $this->applyCollectionPaymentDateScope($q, $filters);

        app(DashboardFilterCascadeService::class)->applyCollectionDimensionFilters($q, $filters);

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

        if (! empty($filters->uens)) {
            $vals = array_values(array_unique(array_map(static fn ($v) => trim((string) $v), $filters->uens)));
            $vals = array_values(array_filter($vals, static fn ($v) => $v !== ''));
            if ($vals !== []) {
                $q->whereIn(DB::raw('TRIM(uen)'), $vals);
            }
        } elseif ($filters->uen) {
            $q->whereRaw('TRIM(uen) = ?', [trim($filters->uen)]);
        }

        if (! empty($filters->regionals)) {
            $vals = array_values(array_unique(array_map(static fn ($v) => trim((string) $v), $filters->regionals)));
            $vals = array_values(array_filter($vals, static fn ($v) => $v !== ''));
            if ($vals !== []) {
                $q->whereIn(DB::raw('TRIM(regional)'), $vals);
            }
        } elseif ($filters->regional) {
            $q->whereRaw('TRIM(regional) = ?', [trim($filters->regional)]);
        }

        if (! empty($filters->channels)) {
            $vals = array_values(array_unique(array_map(static fn ($v) => trim((string) $v), $filters->channels)));
            $vals = array_values(array_filter($vals, static fn ($v) => $v !== ''));
            if ($vals !== []) {
                $q->whereIn(DB::raw('TRIM(channel)'), $vals);
            }
        } elseif ($filters->channel) {
            $q->whereRaw('TRIM(channel) = ?', [trim($filters->channel)]);
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
