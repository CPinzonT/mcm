<?php

namespace App\Services\Dashboard;

use App\Data\DashboardFiltersData;
use App\Services\Dashboard\Concerns\AppliesOperativePortfolioDocuments;
use App\Services\Dashboard\Concerns\AppliesPortfolioPeriodCut;
use App\Services\Risk\Concerns\AppliesLiveDaysOverdue;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class KpiClientDrilldownService
{
    use AppliesLiveDaysOverdue;
    use AppliesOperativePortfolioDocuments;
    use AppliesPortfolioPeriodCut;

    private const ALLOWED_TYPES = ['critical', 'overdue_documents', 'negative', 'concentration_top5'];

    /**
     * @return array{
     *   type:string,title:string,amount_label:string,total_clients:int,total_documents:int,
     *   total_amount:float,rows:array<int,array<string,mixed>>
     * }
     */
    public function build(
        DashboardFiltersData $filters,
        string $type,
        ?string $uenFilter = null,
        ?string $channelFilter = null,
        array $documentTypeFilters = [],
    ): array
    {
        if (! in_array($type, self::ALLOWED_TYPES, true)) {
            throw new \InvalidArgumentException("Drill-down KPI no soportado: {$type}");
        }

        $query = $this->baseActiveQuery($filters);
        $this->applyIndicatorScope($query, $filters, $type);

        if ($type === 'concentration_top5') {
            $topClientIds = (clone $query)
                ->select('pd.client_id')
                ->selectRaw('SUM(pd.pending_amount) as total')
                ->groupBy('pd.client_id')
                ->orderByDesc('total')
                ->limit(5)
                ->pluck('pd.client_id')
                ->map(static fn ($id): int => (int) $id)
                ->all();

            $query->whereIn('pd.client_id', $topClientIds);
        }

        $uenOptions = $this->dimensionOptions(clone $query, 'c.uen');
        $channelOptions = $this->dimensionOptions(clone $query, 'c.channel');
        $documentTypeOptions = $this->dimensionOptions(clone $query, 'pd.document_type');

        if ($uenFilter !== null && trim($uenFilter) !== '') {
            $query->whereRaw('TRIM(c.uen) = ?', [trim($uenFilter)]);
        }
        if ($channelFilter !== null && trim($channelFilter) !== '') {
            $query->whereRaw('TRIM(c.channel) = ?', [trim($channelFilter)]);
        }
        $documentTypeFilters = array_values(array_unique(array_filter(
            array_map(static fn ($value): string => trim((string) $value), $documentTypeFilters),
            static fn (string $value): bool => $value !== '',
        )));
        if ($documentTypeFilters !== []) {
            $query->whereIn(DB::raw('TRIM(pd.document_type)'), $documentTypeFilters);
        }

        [$daysSql, $daysBindings] = $this->liveDaysOverdueBindings($filters);

        $rows = (clone $query)
            ->select([
                'c.id as client_id',
                'c.name as client',
                'c.document_number as nit',
                'c.uen',
                'c.channel',
                DB::raw('COUNT(pd.id) as documents'),
                DB::raw('SUM(pd.pending_amount) as amount'),
            ])
            ->selectRaw("MAX({$daysSql}) as max_days_overdue", $daysBindings)
            ->groupBy('c.id', 'c.name', 'c.document_number', 'c.uen', 'c.channel')
            ->orderByRaw($type === 'negative' ? 'SUM(pd.pending_amount) ASC' : 'SUM(pd.pending_amount) DESC')
            ->get();

        $documentTypesByClient = (clone $query)
            ->select([
                'pd.client_id',
                'pd.document_type',
                DB::raw('COUNT(pd.id) as documents'),
            ])
            ->whereNotNull('pd.document_type')
            ->whereRaw("TRIM(pd.document_type) <> ''")
            ->groupBy('pd.client_id', 'pd.document_type')
            ->orderBy('pd.document_type')
            ->get()
            ->groupBy('client_id')
            ->map(static fn ($items): array => $items
                ->map(static fn ($item): array => [
                    'type' => trim((string) $item->document_type),
                    'documents' => (int) $item->documents,
                ])
                ->all());

        $totalDocuments = (int) $rows->sum('documents');
        $totalAmount = (float) $rows->sum(
            static fn ($row) => $type === 'negative'
                ? abs((float) $row->amount)
                : (float) $row->amount,
        );

        $mappedRows = $rows
            ->map(static function ($row) use ($type, $totalAmount, $documentTypesByClient): array {
                $amount = $type === 'negative'
                    ? abs((float) $row->amount)
                    : (float) $row->amount;

                return [
                    'client_id' => (int) $row->client_id,
                    'client' => $row->client,
                    'nit' => $row->nit,
                    'uen' => $row->uen,
                    'channel' => $row->channel,
                    'documents' => (int) $row->documents,
                    'amount' => $amount,
                    'max_days_overdue' => (int) ($row->max_days_overdue ?? 0),
                    'share_pct' => $totalAmount > 0 ? round($amount / $totalAmount * 100, 1) : 0.0,
                    'document_types' => $documentTypesByClient->get($row->client_id, []),
                ];
            })
            ->all();

        return [
            'type' => $type,
            'title' => match ($type) {
                'critical' => 'Cartera crítica por cliente (>90 días)',
                'overdue_documents' => 'Documentos vencidos por cliente',
                'negative' => 'Saldos negativos por cliente',
                'concentration_top5' => 'Concentración — Top 5 clientes por saldo',
            },
            'amount_label' => match ($type) {
                'negative' => 'Saldo negativo',
                'concentration_top5' => 'Saldo total',
                default => 'Saldo involucrado',
            },
            'total_clients' => count($mappedRows),
            'total_documents' => $totalDocuments,
            'total_amount' => $totalAmount,
            'uen_options' => $uenOptions,
            'channel_options' => $channelOptions,
            'document_type_options' => $documentTypeOptions,
            'rows' => $mappedRows,
        ];
    }

    private function applyIndicatorScope(
        Builder $query,
        DashboardFiltersData $filters,
        string $type,
    ): void {
        if ($type === 'concentration_top5') {
            return;
        }

        if ($type === 'negative') {
            $query->where('pd.pending_amount', '<', 0);

            return;
        }

        $this->applyOperativeDocumentStatus($query);
        $this->whereLiveDaysOverdue(
            $query,
            '>',
            $type === 'critical' ? 90 : 0,
            $filters,
        );
    }

    private function baseActiveQuery(DashboardFiltersData $filters): Builder
    {
        $query = DB::table('portfolio_documents as pd')
            ->join('portfolio_loads as pl', 'pl.id', '=', 'pd.portfolio_load_id')
            ->join('clients as c', 'c.id', '=', 'pd.client_id')
            ->leftJoin('advisors as a', 'a.id', '=', 'pd.advisor_id')
            ->where('pl.status', 'completed')
            ->whereNull('pd.deleted_at');

        $this->applyDashboardPortfolioLoad($query);
        $this->applyPortfolioPeriodCut($query, $filters);
        $this->applyDimensionFilters($query, $filters);

        return $query;
    }

    private function applyDimensionFilters(Builder $query, DashboardFiltersData $filters): void
    {
        $this->applyStringDimension($query, 'c.uen', $filters->uens, $filters->uen);
        $this->applyStringDimension($query, 'c.region', $filters->regionals, $filters->regional);
        $this->applyStringDimension($query, 'c.channel', $filters->channels, $filters->channel);

        app(DashboardFilterCascadeService::class)->applyPortfolioAdvisorConstraint($query, $filters);

        if ($filters->clientId) {
            $query->where('pd.client_id', $filters->clientId);
        }
        if ($filters->riskLevels !== []) {
            $query->whereIn('pd.risk_level', $filters->riskLevels);
        }
        if ($filters->documentTypes !== []) {
            $query->whereIn('pd.document_type', $filters->documentTypes);
        }
    }

    /**
     * @param  string[]  $multiple
     */
    private function applyStringDimension(
        Builder $query,
        string $column,
        array $multiple,
        ?string $single,
    ): void {
        $values = array_values(array_unique(array_filter(
            array_map(static fn ($value) => trim((string) $value), $multiple),
            static fn ($value) => $value !== '',
        )));

        if ($values !== []) {
            $query->whereIn(DB::raw("TRIM({$column})"), $values);

            return;
        }

        if ($single !== null && trim($single) !== '') {
            $query->whereRaw("TRIM({$column}) = ?", [trim($single)]);
        }
    }

    /** @return string[] */
    private function dimensionOptions(Builder $query, string $column): array
    {
        return $query
            ->whereNotNull($column)
            ->whereRaw("TRIM({$column}) <> ''")
            ->selectRaw("TRIM({$column}) as value")
            ->distinct()
            ->orderBy('value')
            ->pluck('value')
            ->map(static fn ($value): string => trim((string) $value))
            ->all();
    }
}
