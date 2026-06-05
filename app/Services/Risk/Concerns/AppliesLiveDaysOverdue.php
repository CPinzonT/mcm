<?php

namespace App\Services\Risk\Concerns;

use App\Data\DashboardFiltersData;
use Carbon\CarbonImmutable;
use Illuminate\Database\Query\Builder;

/**
 * Días de mora en consultas: desde fecha de vencimiento hasta la fecha de consulta (hoy o Hasta del filtro).
 */
trait AppliesLiveDaysOverdue
{
    protected function consultationDate(?DashboardFiltersData $filters = null): string
    {
        if ($filters !== null) {
            return $filters->consultationDate();
        }

        return CarbonImmutable::today()->format('Y-m-d');
    }

    /**
     * @return array{0: string, 1: array<int, string>}
     */
    protected function liveDaysOverdueBindings(?DashboardFiltersData $filters = null, string $dueColumn = 'pd.due_date'): array
    {
        return [
            "GREATEST(0, DATEDIFF(?, DATE({$dueColumn})))",
            [$this->consultationDate($filters)],
        ];
    }

    protected function whereLiveDaysOverdue(Builder $query, string $operator, int $threshold, ?DashboardFiltersData $filters = null, string $dueColumn = 'pd.due_date'): void
    {
        [$sql, $bindings] = $this->liveDaysOverdueBindings($filters, $dueColumn);
        $query->whereRaw("({$sql}) {$operator} ?", array_merge($bindings, [$threshold]));
    }

    protected function whereLiveDaysBetween(Builder $query, int $min, int $max, ?DashboardFiltersData $filters = null, string $dueColumn = 'pd.due_date'): void
    {
        [$sql, $bindings] = $this->liveDaysOverdueBindings($filters, $dueColumn);
        $query->whereRaw("({$sql}) BETWEEN ? AND ?", array_merge($bindings, [$min, $max]));
    }
}
