<?php

namespace App\Services\Dashboard\Concerns;

use App\Data\DashboardFiltersData;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;

/**
 * Acota recaudo por fecha de pago.
 */
trait AppliesCollectionPaymentDateScope
{
    /** @return array{0: string, 1: string} Último mes calendario completamente cerrado. */
    protected function resolveDefaultMonthCutRange(): array
    {
        $closedMonth = Carbon::today()->subMonthNoOverflow();

        return [
            $closedMonth->copy()->startOfMonth()->toDateString(),
            $closedMonth->copy()->endOfMonth()->toDateString(),
        ];
    }

    protected function defaultMonthCutPaymentLabel(): string
    {
        [$start, $end] = $this->resolveDefaultMonthCutRange();
        $from = Carbon::parse($start)->format('d/m/Y');
        $to   = Carbon::parse($end)->format('d/m/Y');

        return $from === $to ? "Corte mes: {$from}" : "Corte mes: {$from} – {$to}";
    }

    protected function applyCollectionPaymentDateScope(
        Builder $query,
        DashboardFiltersData $filters,
        string $column = 'cd.payment_date',
    ): void {
        [$start, $end] = $this->resolveCollectionPaymentDateRange($filters);

        if ($start !== null && $end !== null) {
            $query->whereBetween($column, [$start, $end]);
        }
    }

    /**
     * @return array{0: ?string, 1: ?string} [desde, hasta] YYYY-MM-DD
     */
    protected function resolveCollectionPaymentDateRange(DashboardFiltersData $filters): array
    {
        if ($filters->period) {
            $month = Carbon::parse($filters->period . '-01');

            return [
                $month->copy()->startOfMonth()->toDateString(),
                $month->copy()->endOfMonth()->toDateString(),
            ];
        }

        if ($filters->dateFrom && $filters->dateTo) {
            return [$filters->dateFrom, $filters->dateTo];
        }

        if ($filters->dateTo) {
            $to = Carbon::parse($filters->dateTo);

            return [
                $to->copy()->startOfMonth()->toDateString(),
                $to->toDateString(),
            ];
        }

        if ($filters->dateFrom) {
            $from = Carbon::parse($filters->dateFrom);

            return [
                $from->toDateString(),
                $from->copy()->endOfMonth()->toDateString(),
            ];
        }

        return $this->resolveDefaultMonthCutRange();
    }

    protected function collectionPaymentDateLabel(DashboardFiltersData $filters): string
    {
        [$start, $end] = $this->resolveCollectionPaymentDateRange($filters);

        if ($start === null || $end === null) {
            return 'Pagos: última carga (sin filtro de fechas)';
        }

        $from = Carbon::parse($start)->format('d/m/Y');
        $to   = Carbon::parse($end)->format('d/m/Y');

        if ($from === $to) {
            return "Pagos: {$from}";
        }

        return "Pagos: {$from} – {$to}";
    }
}
