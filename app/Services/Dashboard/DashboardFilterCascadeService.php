<?php

namespace App\Services\Dashboard;

use App\Data\DashboardFiltersData;
use App\Services\Dashboard\Concerns\AppliesOperativePortfolioDocuments;
use App\Services\Dashboard\Concerns\AppliesPortfolioPeriodCut;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Construye consultas sobre cartera activa para poblar filtros en cascada:
 * las opciones de cada dimensión respetan las demás selecciones excepto la propia dimensión.
 */
class DashboardFilterCascadeService
{
    use AppliesOperativePortfolioDocuments;
    use AppliesPortfolioPeriodCut;

    /** @param string[] $omit uno de: uens, channels, advisors, regionals, accounting, document_types, risk_levels, client */
    public function baseQuery(DashboardFiltersData $filters, array $omit = []): \Illuminate\Database\Query\Builder
    {
        $q = DB::table('portfolio_documents as pd')
            ->join('portfolio_loads as pl', 'pl.id', '=', 'pd.portfolio_load_id')
            ->join('clients as c', 'c.id', '=', 'pd.client_id')
            ->leftJoin('advisors as a', 'a.id', '=', 'pd.advisor_id')
            ->where('pl.status', 'completed')
            ->whereNull('pd.deleted_at');
        $this->applyDashboardPortfolioLoad($q);
        $this->applyPortfolioBalanceStatus($q);

        if (! in_array('accounting', $omit, true)) {
            $this->applyPortfolioPeriodCut($q, $filters);
        }

        if (!in_array('uens', $omit, true)) {
            if (!empty($filters->uens)) {
                $vals = $this->trimmedNonEmptyList($filters->uens);
                if ($vals !== []) {
                    $q->whereIn(DB::raw('TRIM(c.uen)'), $vals);
                }
            } elseif ($filters->uen) {
                $q->whereRaw('TRIM(c.uen) = ?', [trim($filters->uen)]);
            }
        }

        if (!in_array('regionals', $omit, true)) {
            if (!empty($filters->regionals)) {
                $vals = $this->trimmedNonEmptyList($filters->regionals);
                if ($vals !== []) {
                    $q->whereIn(DB::raw('TRIM(c.region)'), $vals);
                }
            } elseif ($filters->regional) {
                $q->whereRaw('TRIM(c.region) = ?', [trim($filters->regional)]);
            }
        }

        if (!in_array('channels', $omit, true)) {
            if (!empty($filters->channels)) {
                $vals = $this->trimmedNonEmptyList($filters->channels);
                if ($vals !== []) {
                    $q->whereIn(DB::raw('TRIM(c.channel)'), $vals);
                }
            } elseif ($filters->channel) {
                $q->whereRaw('TRIM(c.channel) = ?', [trim($filters->channel)]);
            }
        }

        if (!in_array('advisors', $omit, true)) {
            $this->applyPortfolioAdvisorConstraint($q, $filters);
        }

        if (!in_array('client', $omit, true) && $filters->clientId) {
            $q->where('pd.client_id', $filters->clientId);
        }

        if (!in_array('risk_levels', $omit, true) && !empty($filters->riskLevels)) {
            $q->whereIn('pd.risk_level', $filters->riskLevels);
        }

        if (!in_array('document_types', $omit, true) && !empty($filters->documentTypes)) {
            $q->whereIn('pd.document_type', $filters->documentTypes);
        }

        return $q;
    }

    public function uenOptions(DashboardFiltersData $filters): array
    {
        return $this->baseQuery($filters, ['uens'])
            ->whereNotNull('c.uen')
            ->where('c.uen', '!=', '')
            ->select('c.uen')
            ->distinct()
            ->orderBy('c.uen')
            ->pluck('c.uen', 'c.uen')
            ->toArray();
    }

    public function channelOptions(DashboardFiltersData $filters): array
    {
        return $this->baseQuery($filters, ['channels'])
            ->whereNotNull('c.channel')
            ->where('c.channel', '!=', '')
            ->select('c.channel')
            ->distinct()
            ->orderBy('c.channel')
            ->pluck('c.channel', 'c.channel')
            ->toArray();
    }

    public function regionalOptions(DashboardFiltersData $filters): array
    {
        return $this->baseQuery($filters, ['regionals'])
            ->whereNotNull('c.region')
            ->where('c.region', '!=', '')
            ->select('c.region')
            ->distinct()
            ->orderBy('c.region')
            ->pluck('c.region', 'c.region')
            ->toArray();
    }

    /**
     * @return array<int|string, string> id => nombre
     */
    public function advisorOptions(DashboardFiltersData $filters, string $search = '', int $limit = 80): array
    {
        $q = $this->baseQuery($filters, ['advisors'])
            ->whereNotNull('pd.advisor_id')
            ->select('pd.advisor_id', 'a.name')
            ->distinct()
            ->orderBy('a.name');

        if ($search !== '') {
            $q->where('a.name', 'like', '%' . $search . '%');
        }

        $rows = $q->limit($limit)->get();

        $out = [];
        foreach ($rows as $row) {
            if ($row->advisor_id && $row->name) {
                $out[(int) $row->advisor_id] = $row->name;
            }
        }

        return $out;
    }

    /**
     * @return array<int|string, string> id => nombre
     */
    public function clientOptions(DashboardFiltersData $filters, string $search = '', int $limit = 50): array
    {
        $q = $this->baseQuery($filters, ['client'])
            ->whereNotNull('c.name')
            ->where('c.name', '!=', '')
            ->select('c.id', 'c.name')
            ->groupBy('c.id', 'c.name')
            ->orderBy('c.name');

        if ($search !== '') {
            $q->where('c.name', 'like', '%' . $search . '%');
        }

        return $q->limit($limit)->pluck('c.name', 'c.id')->toArray();
    }

    public function documentTypeOptions(DashboardFiltersData $filters): array
    {
        return $this->baseQuery($filters, ['document_types'])
            ->whereNotNull('pd.document_type')
            ->where('pd.document_type', '!=', '')
            ->select('pd.document_type')
            ->distinct()
            ->orderBy('pd.document_type')
            ->pluck('pd.document_type', 'pd.document_type')
            ->toArray();
    }

    /**
     * Meses de contabilización (issue_date) en el último corte, para comparación.
     *
     * @return array<string, string> YYYY-MM => YYYY-MM
     */
    public function loadedAccountingMonthOptions(): array
    {
        $q = DB::table('portfolio_documents as pd')
            ->join('portfolio_loads as pl', 'pl.id', '=', 'pd.portfolio_load_id')
            ->where('pl.status', 'completed')
            ->whereNull('pd.deleted_at')
            ->whereNotNull('pd.issue_date');
        $this->applyDashboardPortfolioLoad($q);
        $this->applyPortfolioBalanceStatus($q);

        $this->applyLatestPortfolioCut($q);

        $options = [];
        foreach ($q->selectRaw('DISTINCT LEFT(pd.issue_date, 7) as ym')->orderByDesc('ym')->pluck('ym') as $ym) {
            $ym = trim((string) $ym);
            if ($ym !== '') {
                $options[$ym] = $ym;
            }
        }

        return $options;
    }

    /**
     * Meses de contabilización con documentos bajo el resto de filtros (cascada).
     *
     * @return array<string, string> ym => ym
     */
    public function accountingMonthOptions(DashboardFiltersData $filters): array
    {
        return $this->baseQuery($filters, ['accounting'])
            ->whereNotNull('pd.issue_date')
            ->selectRaw('DISTINCT LEFT(pd.issue_date, 7) as ym')
            ->orderByDesc('ym')
            ->pluck('ym', 'ym')
            ->toArray();
    }

    /**
     * Incluye IDs de asesores cuyo nombre coincide (trim + minúsculas) con los seleccionados,
     * para no perder cartera cuando existen filas duplicadas en `advisors` por variaciones de texto.
     *
     * @param  int[]  $ids
     * @return int[]
     */
    private function expandAdvisorIdsByName(array $ids): array
    {
        $ids = array_values(array_unique(array_filter(
            array_map('intval', $ids),
            static fn (int $id) => $id > 0
        )));
        if ($ids === []) {
            return [];
        }

        $norms = [];
        foreach (DB::table('advisors')->whereIn('id', $ids)->pluck('name') as $name) {
            $n = mb_strtolower(trim((string) $name));
            if ($n !== '') {
                $norms[$n] = true;
            }
        }

        if ($norms === []) {
            return $ids;
        }

        return DB::table('advisors')
            ->where(function ($q) use ($ids, $norms) {
                $q->whereIn('id', $ids);
                foreach (array_keys($norms) as $norm) {
                    $q->orWhereRaw('TRIM(LOWER(name)) = ?', [$norm]);
                }
            })
            ->pluck('id')
            ->map(static fn ($v) => (int) $v)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * IDs de asesor a aplicar en cartera activa (multi-selección o comparación por advisorId),
     * expandiendo por nombre para alinear con cargas que generaron duplicados en `advisors`.
     *
     * @return int[]
     */
    public function resolveAdvisorIdsForActivePortfolio(DashboardFiltersData $filters): array
    {
        if (!empty($filters->advisors)) {
            return $this->expandAdvisorIdsByName($filters->advisors);
        }
        if ($filters->advisorId) {
            return $this->expandAdvisorIdsByName([(int) $filters->advisorId]);
        }

        return [];
    }

    /**
     * Filtra cartera por asesor: documentos con pd.advisor_id en los IDs (expandidos por nombre)
     * o, si el documento no tiene asesor en cartera, filas de recaudo activas vinculadas al mismo
     * documento cuyo seller_name coincide con el nombre de alguno de los asesores seleccionados.
     */
    public function applyPortfolioAdvisorConstraint(Builder $query, DashboardFiltersData $filters): void
    {
        $advIds = $this->resolveAdvisorIdsForActivePortfolio($filters);
        if ($advIds === []) {
            return;
        }

        $nameNorms = $this->distinctAdvisorNameNorms($advIds);
        $query->where(function (Builder $w) use ($advIds, $nameNorms) {
            $w->whereIn('pd.advisor_id', $advIds);

            if ($nameNorms === []) {
                return;
            }

            foreach ($nameNorms as $norm) {
                $w->orWhereRaw('TRIM(LOWER(a.name)) = ?', [$norm]);
            }

            $w->orWhere(function (Builder $w2) use ($nameNorms) {
                $w2->whereNull('pd.advisor_id')
                    ->whereExists(function (Builder $sub) use ($nameNorms) {
                        $sub->selectRaw('1')
                            ->from('collection_details as cd')
                            ->join('collection_loads as cl', 'cl.id', '=', 'cd.collection_load_id')
                            ->whereColumn('cd.portfolio_document_id', 'pd.id')
                            ->where('cl.is_active', true)
                            ->where('cl.status', 'completed')
                            ->where(function (Builder $sn) use ($nameNorms) {
                                foreach ($nameNorms as $norm) {
                                    $sn->orWhereRaw('TRIM(LOWER(cd.seller_name)) = ?', [$norm]);
                                }
                            });
                    });
            });
        });
    }

    /**
     * Filtros de dimensión sobre collection_details de la carga activa (UEN, canal, regional, asesor, cliente).
     * Las fechas Desde/Hasta del dashboard son de contabilización de cartera, no de pago de recaudo.
     */
    public function applyCollectionDimensionFilters(Builder $query, DashboardFiltersData $filters): void
    {
        if (!empty($filters->uens)) {
            $vals = array_values(array_unique(array_map(static fn ($v) => trim((string) $v), $filters->uens)));
            $vals = array_values(array_filter($vals, static fn ($v) => $v !== ''));
            if ($vals !== []) {
                $query->where(function (Builder $w) use ($vals) {
                    $w->whereIn(DB::raw('TRIM(cd.uen)'), $vals)
                        ->orWhereIn(DB::raw('TRIM(c.uen)'), $vals);
                });
            }
        } elseif ($filters->uen) {
            $uen = trim($filters->uen);
            $query->where(function (Builder $w) use ($uen) {
                $w->whereRaw('TRIM(cd.uen) = ?', [$uen])
                    ->orWhereRaw('TRIM(c.uen) = ?', [$uen]);
            });
        }

        if (!empty($filters->regionals)) {
            $vals = array_values(array_unique(array_map(static fn ($v) => trim((string) $v), $filters->regionals)));
            $vals = array_values(array_filter($vals, static fn ($v) => $v !== ''));
            if ($vals !== []) {
                $query->where(function (Builder $w) use ($vals) {
                    $w->whereIn(DB::raw('TRIM(cd.regional)'), $vals)
                        ->orWhereIn(DB::raw('TRIM(c.region)'), $vals);
                });
            }
        } elseif ($filters->regional) {
            $regional = trim($filters->regional);
            $query->where(function (Builder $w) use ($regional) {
                $w->whereRaw('TRIM(cd.regional) = ?', [$regional])
                    ->orWhereRaw('TRIM(c.region) = ?', [$regional]);
            });
        }

        if (!empty($filters->channels)) {
            $vals = array_values(array_unique(array_map(static fn ($v) => trim((string) $v), $filters->channels)));
            $vals = array_values(array_filter($vals, static fn ($v) => $v !== ''));
            if ($vals !== []) {
                $query->where(function (Builder $w) use ($vals) {
                    $w->whereIn(DB::raw('TRIM(cd.channel)'), $vals)
                        ->orWhereIn(DB::raw('TRIM(c.channel)'), $vals);
                });
            }
        } elseif ($filters->channel) {
            $channel = trim($filters->channel);
            $query->where(function (Builder $w) use ($channel) {
                $w->whereRaw('TRIM(cd.channel) = ?', [$channel])
                    ->orWhereRaw('TRIM(c.channel) = ?', [$channel]);
            });
        }

        if ($filters->clientId) {
            $query->where('cd.client_id', $filters->clientId);
        }

        $this->applyCollectionAdvisorConstraint($query, $filters);
    }

    /**
     * Filtra recaudo por vendedor/asesor (seller_name en el detalle de la carga activa).
     */
    public function applyCollectionAdvisorConstraint(Builder $query, DashboardFiltersData $filters): void
    {
        $advIds = $this->resolveAdvisorIdsForActivePortfolio($filters);
        if ($advIds === []) {
            return;
        }

        $nameNorms = $this->distinctAdvisorNameNorms($advIds);
        if ($nameNorms === []) {
            return;
        }

        $query->where(function (Builder $w) use ($nameNorms) {
            foreach ($nameNorms as $norm) {
                $w->orWhereRaw('TRIM(LOWER(cd.seller_name)) = ?', [$norm]);
            }
        });
    }

    /**
     * @param  int[]  $advisorIds
     * @return string[] nombres normalizados únicos (trim + mb_strtolower)
     */
    private function distinctAdvisorNameNorms(array $advisorIds): array
    {
        if ($advisorIds === []) {
            return [];
        }
        $seen = [];
        foreach (DB::table('advisors')->whereIn('id', $advisorIds)->pluck('name') as $name) {
            $n = mb_strtolower(trim((string) $name));
            if ($n !== '') {
                $seen[$n] = true;
            }
        }

        return array_keys($seen);
    }

    /**
     * @param  string[]  $selected
     * @param  array<string|int, mixed>  $allowedKeyToLabel  p. ej. resultado de pluck('c.uen','c.uen')
     * @return string[] valores canónicos (clave en BD) por cada trim coincidente
     */
    public function pruneDimensionsToAllowedKeys(array $selected, array $allowedKeyToLabel): array
    {
        $canonicalByTrim = [];
        foreach (array_keys($allowedKeyToLabel) as $k) {
            $t = trim((string) $k);
            if ($t === '') {
                continue;
            }
            if (!array_key_exists($t, $canonicalByTrim)) {
                $canonicalByTrim[$t] = (string) $k;
            }
        }
        $out = [];
        foreach ($selected as $sel) {
            $t = trim((string) $sel);
            if ($t !== '' && isset($canonicalByTrim[$t])) {
                $out[] = $canonicalByTrim[$t];
            }
        }

        return array_values(array_unique($out));
    }

    /**
     * @param  array<int, string>  $values
     * @return string[]
     */
    private function trimmedNonEmptyList(array $values): array
    {
        $out = [];
        foreach ($values as $v) {
            $s = trim((string) $v);
            if ($s !== '') {
                $out[] = $s;
            }
        }

        return array_values(array_unique($out));
    }
}
