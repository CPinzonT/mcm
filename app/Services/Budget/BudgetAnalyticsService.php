<?php

namespace App\Services\Budget;

use App\Data\BudgetFiltersData;
use App\Models\BudgetLoad;
use Carbon\CarbonImmutable;

class BudgetAnalyticsService
{
    public function __construct(
        private readonly BudgetFilterQueryService $filterQuery,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function summarize(BudgetFiltersData $filters): array
    {
        $base = $this->filterQuery->baseQuery($filters);

        $budgetTotal = (float) (clone $base)->sum('budget_amount');
        $recaudoTotal = (float) (clone $base)->sum('collection_amount');
        $rowCount = (int) (clone $base)->count();
        $balanceDue = (float) (clone $base)->sum('balance_due');

        $cumplimiento = ($budgetTotal > 0 && $recaudoTotal > 0)
            ? round($recaudoTotal / $budgetTotal * 100, 2)
            : ($budgetTotal > 0 ? 0.0 : null);

        $brecha = round($recaudoTotal - $budgetTotal, 2);

        $byCategory = (clone $base)
            ->selectRaw('category, SUM(budget_amount) as ppto, SUM(collection_amount) as recaudo')
            ->groupBy('category')
            ->orderByDesc('ppto')
            ->limit(12)
            ->get()
            ->map(fn ($r) => [
                'label'   => $r->category ?: 'Sin categoría',
                'ppto'    => (float) $r->ppto,
                'recaudo' => (float) $r->recaudo,
            ])
            ->all();

        $byTransactionType = (clone $base)
            ->selectRaw('transaction_type, SUM(budget_amount) as ppto, SUM(collection_amount) as recaudo')
            ->groupBy('transaction_type')
            ->orderByDesc('ppto')
            ->limit(12)
            ->get()
            ->map(fn ($r) => [
                'label'   => $r->transaction_type ?: 'Sin tipo',
                'ppto'    => (float) $r->ppto,
                'recaudo' => (float) $r->recaudo,
            ])
            ->all();

        $latestLoad = BudgetLoad::query()
            ->where('status', 'completed')
            ->orderByDesc('processed_at')
            ->orderByDesc('id')
            ->first();

        $periodsWithBudget = $this->filterQuery->periodOptions();

        return [
            'period_label'        => $this->periodLabel($filters),
            'budget_total'        => $budgetTotal > 0 ? $budgetTotal : null,
            'recaudo_total'       => $recaudoTotal > 0 ? $recaudoTotal : null,
            'cumplimiento_pct'    => $cumplimiento,
            'brecha'              => $brecha,
            'balance_due_total'   => $balanceDue > 0 ? $balanceDue : null,
            'budget_rows'         => $rowCount,
            'periods_count'       => count($periodsWithBudget),
            'by_category'         => $byCategory,
            'by_transaction_type' => $byTransactionType,
            'latest_load'         => $latestLoad ? [
                'reference'  => $latestLoad->reference,
                'filename'   => $latestLoad->original_filename,
                'period_key' => $latestLoad->period_key,
                'valid_rows' => $latestLoad->valid_rows,
                'total'      => (float) $latestLoad->total_amount,
                'at'         => $latestLoad->processed_at?->format('d/m/Y H:i'),
            ] : null,
            'has_budget_data'     => $rowCount > 0,
        ];
    }

    private function periodLabel(BudgetFiltersData $filters): string
    {
        if ($filters->periods !== []) {
            if (count($filters->periods) === 1) {
                return CarbonImmutable::parse($filters->periods[0] . '-01')->translatedFormat('F Y');
            }

            return count($filters->periods) . ' períodos';
        }

        return 'Todos los períodos cargados';
    }
}
