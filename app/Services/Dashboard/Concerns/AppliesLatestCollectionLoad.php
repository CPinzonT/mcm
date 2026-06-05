<?php

namespace App\Services\Dashboard\Concerns;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use stdClass;

/**
 * Métricas de recaudo: una sola carga activa completada (la más reciente por procesamiento).
 */
trait AppliesLatestCollectionLoad
{
  /** @var stdClass|null{id:int,reference:?string,total_collected:?string,processed_at:?string,period_date:?string,period_key:?string} */
    private static ?stdClass $dashboardCollectionLoadCache = null;

    protected function resolveDashboardCollectionLoad(): ?stdClass
    {
        if (self::$dashboardCollectionLoadCache !== null) {
            return self::$dashboardCollectionLoadCache;
        }

        self::$dashboardCollectionLoadCache = DB::table('collection_loads')
            ->where('is_active', true)
            ->where('status', 'completed')
            ->orderByDesc('processed_at')
            ->orderByDesc('version')
            ->orderByDesc('id')
            ->first(['id', 'reference', 'total_collected', 'processed_at', 'period_date', 'period_key']);

        return self::$dashboardCollectionLoadCache;
    }

    protected function latestActiveCollectionLoadId(): ?int
    {
        $load = $this->resolveDashboardCollectionLoad();

        return $load ? (int) $load->id : null;
    }

    protected function applyLatestCollectionLoad(Builder $query, string $loadIdColumn = 'cd.collection_load_id'): void
    {
        $latestLoadId = $this->latestActiveCollectionLoadId();

        if ($latestLoadId) {
            $query->where($loadIdColumn, $latestLoadId);
        } else {
            $query->whereRaw('1 = 0');
        }
    }
}
