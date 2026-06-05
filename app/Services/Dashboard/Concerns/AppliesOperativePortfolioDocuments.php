<?php

namespace App\Services\Dashboard\Concerns;

use App\Models\PortfolioDocument;
use Illuminate\Database\Query\Builder;

trait AppliesOperativePortfolioDocuments
{
    protected function applyOperativeDocumentStatus(Builder $query, string $statusColumn = 'pd.status'): void
    {
        $query->whereIn($statusColumn, PortfolioDocument::OPERATIVE_STATUSES);
    }

    /** Total cartera alineado con total_pending_amount de la carga (incluye pagados con saldo negativo). */
    protected function applyPortfolioBalanceStatus(Builder $query, string $statusColumn = 'pd.status'): void
    {
        $query->whereIn($statusColumn, PortfolioDocument::BALANCE_STATUSES);
    }
}
