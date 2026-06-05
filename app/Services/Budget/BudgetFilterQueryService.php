<?php

namespace App\Services\Budget;

use App\Data\BudgetFiltersData;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class BudgetFilterQueryService
{
    public function baseQuery(?BudgetFiltersData $filters = null): Builder
    {
        $q = \App\Models\BudgetRow::query()
            ->whereHas('budgetLoad', fn ($l) => $l->where('status', 'completed'));

        if ($filters === null) {
            return $q;
        }

        $this->applyFiltersToEloquent($q, $filters);

        return $q;
    }

    /**
     * @return array<string, string>
     */
    public function periodOptions(?BudgetFiltersData $filters = null): array
    {
        $q = DB::table('budget_rows as br')
            ->join('budget_loads as bl', 'bl.id', '=', 'br.budget_load_id')
            ->where('bl.status', 'completed')
            ->whereNotNull('br.period_key');

        if ($filters) {
            $this->applyFiltersToQueryBuilder($q, $filters, skipPeriod: true);
        }

        return $q->selectRaw('DISTINCT br.period_key as ym')
            ->orderByDesc('ym')
            ->pluck('ym', 'ym')
            ->all();
    }

    /**
     * @return array<string, string>
     */
    public function clientOptions(?BudgetFiltersData $filters = null, ?string $search = null, int $limit = 150): array
    {
        return $this->distinctTextOptions('client_name', $filters, $search, $limit, skipClient: true);
    }

    /**
     * @return array<string, string>
     */
    public function regionalOptions(?BudgetFiltersData $filters = null): array
    {
        return $this->distinctColumnOptions('regional', $filters, skipRegional: true);
    }

    /**
     * @return array<string, string>
     */
    public function channelOptions(?BudgetFiltersData $filters = null): array
    {
        return $this->distinctColumnOptions('channel', $filters, skipChannel: true);
    }

    /**
     * @return array<string, string>
     */
    public function sellerOptions(?BudgetFiltersData $filters = null, ?string $search = null, int $limit = 150): array
    {
        return $this->distinctTextOptions('seller_name', $filters, $search, $limit, skipSeller: true);
    }

    /**
     * @return array<string, string>
     */
    public function transactionTypeOptions(?BudgetFiltersData $filters = null): array
    {
        return $this->distinctColumnOptions('transaction_type', $filters, skipTransactionType: true);
    }

    /**
     * @return array<string, string>
     */
    public function categoryOptions(?BudgetFiltersData $filters = null): array
    {
        return $this->distinctColumnOptions('category', $filters, skipCategory: true);
    }

    /**
     * @return array<string, string>
     */
    private function distinctColumnOptions(
        string $column,
        ?BudgetFiltersData $filters,
        bool $skipPeriod = false,
        bool $skipClient = false,
        bool $skipRegional = false,
        bool $skipChannel = false,
        bool $skipSeller = false,
        bool $skipTransactionType = false,
        bool $skipCategory = false,
    ): array {
        $q = DB::table('budget_rows as br')
            ->join('budget_loads as bl', 'bl.id', '=', 'br.budget_load_id')
            ->where('bl.status', 'completed')
            ->whereNotNull("br.{$column}")
            ->where("br.{$column}", '!=', '');

        if ($filters) {
            $this->applyFiltersToQueryBuilder(
                $q,
                $filters,
                skipPeriod: $skipPeriod,
                skipClient: $skipClient,
                skipRegional: $skipRegional,
                skipChannel: $skipChannel,
                skipSeller: $skipSeller,
                skipTransactionType: $skipTransactionType,
                skipCategory: $skipCategory,
            );
        }

        return $q->selectRaw("DISTINCT br.{$column} as val")
            ->orderBy('val')
            ->pluck('val', 'val')
            ->all();
    }

    /**
     * @return array<string, string>
     */
    private function distinctTextOptions(
        string $column,
        ?BudgetFiltersData $filters,
        ?string $search,
        int $limit,
        bool $skipClient = false,
        bool $skipSeller = false,
    ): array {
        $q = DB::table('budget_rows as br')
            ->join('budget_loads as bl', 'bl.id', '=', 'br.budget_load_id')
            ->where('bl.status', 'completed')
            ->whereNotNull("br.{$column}")
            ->where("br.{$column}", '!=', '');

        if ($filters) {
            $skipClientFlag = $column === 'client_name' ? $skipClient : false;
            $skipSellerFlag = $column === 'seller_name' ? $skipSeller : false;
            $this->applyFiltersToQueryBuilder(
                $q,
                $filters,
                skipClient: $skipClientFlag,
                skipSeller: $skipSellerFlag,
            );
        }

        if ($search !== null && trim($search) !== '') {
            $q->where("br.{$column}", 'like', '%' . trim($search) . '%');
        }

        return $q->selectRaw("DISTINCT br.{$column} as val")
            ->orderBy('val')
            ->limit($limit)
            ->pluck('val', 'val')
            ->all();
    }

    private function applyFiltersToEloquent(Builder $q, BudgetFiltersData $filters): void
    {
        if ($filters->periods !== []) {
            $q->whereIn('period_key', $filters->periods);
        }

        if ($filters->clients !== []) {
            $q->whereIn('client_name', $filters->clients);
        }

        if ($filters->regionals !== []) {
            $q->whereIn('regional', $filters->regionals);
        }

        if ($filters->channels !== []) {
            $q->whereIn('channel', $filters->channels);
        }

        if ($filters->sellers !== []) {
            $q->whereIn('seller_name', $filters->sellers);
        }

        if ($filters->transactionTypes !== []) {
            $q->whereIn('transaction_type', $filters->transactionTypes);
        }

        if ($filters->categories !== []) {
            $q->whereIn('category', $filters->categories);
        }

        if ($filters->dateFrom && $filters->dateTo) {
            $q->whereBetween($filters->dateField, [$filters->dateFrom, $filters->dateTo]);
        } elseif ($filters->dateFrom) {
            $q->where($filters->dateField, '>=', $filters->dateFrom);
        } elseif ($filters->dateTo) {
            $q->where($filters->dateField, '<=', $filters->dateTo);
        }
    }

    private function applyFiltersToQueryBuilder(
        $q,
        BudgetFiltersData $filters,
        bool $skipPeriod = false,
        bool $skipClient = false,
        bool $skipRegional = false,
        bool $skipChannel = false,
        bool $skipSeller = false,
        bool $skipTransactionType = false,
        bool $skipCategory = false,
    ): void {
        if (! $skipPeriod && $filters->periods !== []) {
            $q->whereIn('br.period_key', $filters->periods);
        }
        if (! $skipClient && $filters->clients !== []) {
            $q->whereIn('br.client_name', $filters->clients);
        }
        if (! $skipRegional && $filters->regionals !== []) {
            $q->whereIn('br.regional', $filters->regionals);
        }
        if (! $skipChannel && $filters->channels !== []) {
            $q->whereIn('br.channel', $filters->channels);
        }
        if (! $skipSeller && $filters->sellers !== []) {
            $q->whereIn('br.seller_name', $filters->sellers);
        }
        if (! $skipTransactionType && $filters->transactionTypes !== []) {
            $q->whereIn('br.transaction_type', $filters->transactionTypes);
        }
        if (! $skipCategory && $filters->categories !== []) {
            $q->whereIn('br.category', $filters->categories);
        }
        if ($filters->dateFrom && $filters->dateTo) {
            $q->whereBetween("br.{$filters->dateField}", [$filters->dateFrom, $filters->dateTo]);
        } elseif ($filters->dateFrom) {
            $q->where("br.{$filters->dateField}", '>=', $filters->dateFrom);
        } elseif ($filters->dateTo) {
            $q->where("br.{$filters->dateField}", '<=', $filters->dateTo);
        }
    }
}
