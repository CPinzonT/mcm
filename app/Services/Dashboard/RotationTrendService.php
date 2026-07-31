<?php

namespace App\Services\Dashboard;

use App\Data\DashboardFiltersData;
use App\Services\Clients\ClientSalesRotationService;
use App\Services\Dashboard\Concerns\AppliesOperativePortfolioDocuments;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class RotationTrendService
{
    use AppliesOperativePortfolioDocuments;

    /** @return array<string, mixed> */
    public function build(DashboardFiltersData $filters): array
    {
        $loadsQuery = DB::table('portfolio_loads')
            ->where('status', 'completed')
            ->whereNotNull('period_date')
            ->orderByDesc('period_date')
            ->orderByDesc('version')
            ->orderByDesc('id');

        $cutoff = $filters->period
            ? Carbon::parse($filters->period . '-01')->endOfMonth()
            : ($filters->dateTo
                ? Carbon::parse($filters->dateTo)->endOfMonth()
                : ($filters->dateFrom ? Carbon::parse($filters->dateFrom)->endOfMonth() : null));
        if ($cutoff !== null) {
            $loadsQuery->whereDate('period_date', '<=', $cutoff->toDateString());
        }

        $loads = $loadsQuery
            ->get(['id', 'period_date', 'version'])
            ->unique(static fn ($load): string => substr((string) $load->period_date, 0, 7))
            ->take(12)
            ->sortBy('period_date')
            ->values();

        $rows = $loads->map(function ($load) use ($filters): array {
            $portfolio = $this->portfolioQuery((int) $load->id, $filters);
            $balance = (float) (clone $portfolio)->sum('pd.pending_amount');
            $clientIds = (clone $portfolio)
                ->whereNotNull('pd.client_id')
                ->distinct()
                ->pluck('pd.client_id')
                ->map(static fn ($id): int => (int) $id)
                ->all();
            $asOf = Carbon::parse($load->period_date)->endOfMonth();
            $sales = $this->salesForClients($clientIds, $asOf);
            $rotation = app(ClientSalesRotationService::class)
                ->rotationDaysFromSales($balance, $sales);

            return [
                'period' => substr((string) $load->period_date, 0, 7),
                'label' => Carbon::parse($load->period_date)->translatedFormat('M Y'),
                'rotation' => $rotation,
                'portfolio' => $balance,
                'sales_12_months' => $sales,
            ];
        })->all();

        $previous = null;
        foreach ($rows as &$row) {
            $current = $row['rotation'];
            $row['change_pct'] = $current !== null && $previous !== null && $previous > 0
                ? round(($current - $previous) / $previous * 100, 1)
                : null;
            if ($current !== null) {
                $previous = $current;
            }
        }
        unset($row);

        return [
            'title' => 'Variación mensual de la rotación de cartera',
            'formula' => '(cartera del corte / ventas de los 12 meses anteriores) × 360',
            'rows' => $rows,
            'labels' => array_column($rows, 'label'),
            'values' => array_column($rows, 'rotation'),
            'changes' => array_column($rows, 'change_pct'),
        ];
    }

    private function portfolioQuery(int $loadId, DashboardFiltersData $filters): Builder
    {
        $query = DB::table('portfolio_documents as pd')
            ->join('clients as c', 'c.id', '=', 'pd.client_id')
            ->leftJoin('advisors as a', 'a.id', '=', 'pd.advisor_id')
            ->where('pd.portfolio_load_id', $loadId)
            ->whereNull('pd.deleted_at');
        $this->applyPortfolioBalanceStatus($query);

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

        return $query;
    }

    /** @param int[] $clientIds */
    private function salesForClients(array $clientIds, Carbon $asOf): float
    {
        if ($clientIds === []) {
            return 0.0;
        }

        $identities = DB::table('clients')
            ->whereIn('id', $clientIds)
            ->get(['code', 'document_number']);
        $codes = $identities->pluck('code')->map(fn ($v) => trim((string) $v))->filter()->unique()->values()->all();
        $nits = $identities->pluck('document_number')->map(fn ($v) => trim((string) $v))->filter()->unique()->values()->all();
        $from = $asOf->copy()->subMonths(12)->startOfDay();

        return (float) DB::table('sales_rows as sr')
            ->join('sales_loads as sl', 'sl.id', '=', 'sr.sales_load_id')
            ->where('sl.status', 'completed')
            ->whereBetween('sr.sale_date', [$from->toDateString(), $asOf->toDateString()])
            ->where(function (Builder $query) use ($clientIds, $codes, $nits): void {
                $query->whereIn('sr.client_id', $clientIds);
                if ($codes !== []) {
                    $query->orWhereIn('sr.client_code', $codes);
                }
                if ($nits !== []) {
                    $query->orWhereIn('sr.client_nit', $nits);
                }
            })
            ->sum('sr.sale_amount');
    }

    /** @param string[] $multiple */
    private function applyStringDimension(
        Builder $query,
        string $column,
        array $multiple,
        ?string $single,
    ): void {
        $values = array_values(array_unique(array_filter(
            array_map(static fn ($value): string => trim((string) $value), $multiple),
        )));

        if ($values !== []) {
            $query->whereIn(DB::raw("TRIM({$column})"), $values);
        } elseif ($single !== null && trim($single) !== '') {
            $query->whereRaw("TRIM({$column}) = ?", [trim($single)]);
        }
    }
}
