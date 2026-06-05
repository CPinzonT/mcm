<?php

namespace App\Services\Dashboard\Concerns;

use App\Data\DashboardFiltersData;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use stdClass;

/**
 * Cartera del dashboard:
 * - Una sola carga: la completada activa más reciente (period_date + version).
 *   Varias cargas pueden tener is_active (marzo y mayo); el tablero solo usa la última.
 * - Snapshot: period_date de esa carga.
 * - Rango Desde/Hasta → issue_date (contabilización).
 * - Documentos operativos (activo, parcial, en proceso).
 */
trait AppliesPortfolioPeriodCut
{
    use AppliesOperativePortfolioDocuments;

    /** @var stdClass|null{id:int,period_date:?string,period_key:?string,total_pending_amount:?string,document_count:?int} */
    private static ?stdClass $dashboardPortfolioLoadCache = null;

    /**
     * Carga de cartera que gobierna el dashboard (mayo si existe mayo activo, no suma marzo+mayo).
     *
     * @return stdClass|null{id:int,period_date:?string,period_key:?string,total_pending_amount:?string,document_count:?int}
     */
    protected function resolveDashboardPortfolioLoad(): ?stdClass
    {
        if (self::$dashboardPortfolioLoadCache !== null) {
            return self::$dashboardPortfolioLoadCache;
        }

        self::$dashboardPortfolioLoadCache = DB::table('portfolio_loads')
            ->where('is_active', true)
            ->where('status', 'completed')
            ->orderByDesc('period_date')
            ->orderByDesc('version')
            ->first(['id', 'period_date', 'period_key', 'total_pending_amount', 'document_count']);

        return self::$dashboardPortfolioLoadCache;
    }

    protected function applyDashboardPortfolioLoad(Builder $query): void
    {
        $load = $this->resolveDashboardPortfolioLoad();

        if ($load) {
            $query->where('pl.id', (int) $load->id);
        } else {
            $query->whereRaw('1 = 0');
        }
    }

    protected function applyPortfolioPeriodCut(Builder $query, DashboardFiltersData $filters, string $accountingColumn = 'pd.issue_date'): void
    {
        $this->applyLatestPortfolioCut($query);

        if ($filters->period) {
            $start = \Carbon\Carbon::parse($filters->period . '-01')->startOfMonth()->toDateString();
            $end   = \Carbon\Carbon::parse($filters->period . '-01')->endOfMonth()->toDateString();
            $query->whereBetween($accountingColumn, [$start, $end]);

            return;
        }

        if ($filters->dateFrom && $filters->dateTo) {
            $query->whereBetween($accountingColumn, [$filters->dateFrom, $filters->dateTo]);

            return;
        }

        if ($filters->dateTo) {
            $to = \Carbon\Carbon::parse($filters->dateTo);
            $query->whereBetween($accountingColumn, [
                $to->copy()->startOfMonth()->toDateString(),
                $to->toDateString(),
            ]);

            return;
        }

        if ($filters->dateFrom) {
            $from = \Carbon\Carbon::parse($filters->dateFrom);
            $query->whereBetween($accountingColumn, [
                $from->toDateString(),
                $from->copy()->endOfMonth()->toDateString(),
            ]);
        }
    }

    /** Documentos del corte (period_date) de la carga más reciente. */
    protected function applyLatestPortfolioCut(Builder $query, string $periodColumn = 'pd.period_date'): void
    {
        $load = $this->resolveDashboardPortfolioLoad();

        if ($load?->period_date) {
            $query->whereDate($periodColumn, $load->period_date);
        }
    }
}
