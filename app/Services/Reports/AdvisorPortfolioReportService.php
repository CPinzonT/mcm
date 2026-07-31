<?php

namespace App\Services\Reports;

use App\Models\PortfolioDocument;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AdvisorPortfolioReportService
{
    private ?int $activeLoadId = null;

    private bool $activeLoadResolved = false;

    /**
     * @return array<int, object>
     */
    public function advisorSummaries(
        string $periodFrom = '',
        string $periodTo = '',
        string $uen = '',
        string $channel = '',
    ): array
    {
        $portfolioRows = $this->portfolioBase($periodFrom, $periodTo, $uen, $channel)
            ->leftJoin('advisors as a', 'a.id', '=', 'pd.advisor_id')
            ->select([
                'pd.advisor_id',
                DB::raw('COALESCE(a.name, "Sin asignar") as advisor'),
                DB::raw('COUNT(DISTINCT c.id) as clientes'),
                DB::raw('COUNT(pd.id) as documentos'),
                DB::raw('SUM(pd.pending_amount) as saldo_total'),
                DB::raw('SUM(CASE WHEN pd.days_overdue > 0 THEN pd.pending_amount ELSE 0 END) as saldo_vencido'),
            ])
            ->groupBy('pd.advisor_id', 'a.name')
            ->orderByDesc('saldo_total')
            ->get();

        $managementCounts = $this->managementBase($periodFrom, $periodTo, $uen, $channel)
            ->select('ml.advisor_id', DB::raw('COUNT(*) as total'))
            ->groupBy('ml.advisor_id')
            ->get()
            ->keyBy(fn ($row) => $this->advisorKey($row->advisor_id));

        return $portfolioRows
            ->map(function ($row) use ($managementCounts): object {
                $key = $this->advisorKey($row->advisor_id);
                $row->advisor_key = $key;
                $row->advisor_id = $row->advisor_id !== null ? (int) $row->advisor_id : null;
                $row->clientes = (int) $row->clientes;
                $row->documentos = (int) $row->documentos;
                $row->saldo_total = (float) $row->saldo_total;
                $row->saldo_vencido = (float) $row->saldo_vencido;
                $row->gestiones = (int) ($managementCounts->get($key)?->total ?? 0);

                return $row;
            })
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function clientSummaries(
        string $advisorKey,
        string $periodFrom = '',
        string $periodTo = '',
        string $uen = '',
        string $channel = '',
    ): array {
        $query = $this->portfolioBase($periodFrom, $periodTo, $uen, $channel);
        $this->applyAdvisor($query, $advisorKey, 'pd.advisor_id');

        $clients = $query
            ->select([
                'c.id as client_id',
                'c.name as client',
                'c.document_number as nit',
                'c.uen',
                'c.channel',
                DB::raw('COUNT(pd.id) as documentos'),
                DB::raw('SUM(pd.pending_amount) as saldo_total'),
                DB::raw('SUM(CASE WHEN pd.days_overdue > 0 THEN pd.pending_amount ELSE 0 END) as saldo_vencido'),
            ])
            ->groupBy('c.id', 'c.name', 'c.document_number', 'c.uen', 'c.channel')
            ->orderByDesc('saldo_total')
            ->get();

        $clientIds = $clients->pluck('client_id')->map(static fn ($id) => (int) $id)->all();
        $managementCounts = collect();

        if ($clientIds !== []) {
            $managementQuery = $this->managementBase($periodFrom, $periodTo, $uen, $channel)
                ->whereIn('ml.client_id', $clientIds);
            $this->applyAdvisor($managementQuery, $advisorKey, 'ml.advisor_id');

            $managementCounts = $managementQuery
                ->select('ml.client_id', DB::raw('COUNT(*) as total'))
                ->groupBy('ml.client_id')
                ->pluck('total', 'ml.client_id');
        }

        return $clients
            ->map(static function ($row) use ($managementCounts): array {
                return [
                    'client_id' => (int) $row->client_id,
                    'client' => $row->client,
                    'nit' => $row->nit,
                    'uen' => $row->uen,
                    'channel' => $row->channel,
                    'documentos' => (int) $row->documentos,
                    'saldo_total' => (float) $row->saldo_total,
                    'saldo_vencido' => (float) $row->saldo_vencido,
                    'gestiones' => (int) ($managementCounts->get($row->client_id) ?? 0),
                ];
            })
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function documentRows(
        string $advisorKey,
        int $clientId,
        string $periodFrom = '',
        string $periodTo = '',
        string $uen = '',
        string $channel = '',
    ): array {
        $query = $this->portfolioBase($periodFrom, $periodTo, $uen, $channel)
            ->where('pd.client_id', $clientId);
        $this->applyAdvisor($query, $advisorKey, 'pd.advisor_id');

        $documents = $query
            ->select([
                'pd.id',
                'pd.document_number',
                'pd.document_type',
                'pd.issue_date',
                'pd.due_date',
                'pd.days_overdue',
                'pd.original_amount',
                'pd.pending_amount',
                'pd.status',
            ])
            ->orderByDesc('pd.pending_amount')
            ->get();

        $documentIds = $documents->pluck('id')->map(static fn ($id) => (int) $id)->all();
        $managements = $this->managementRowsForDocuments(
            $advisorKey,
            $clientId,
            $documentIds,
            $periodFrom,
            $periodTo,
            $uen,
            $channel,
        );

        $rows = $documents
            ->map(function ($document) use ($managements): array {
                /** @var Collection<int, object> $logs */
                $logs = $managements->get((int) $document->id, collect());
                $latest = $logs->first();

                return [
                    'document_key' => (string) $document->id,
                    'document_id' => (int) $document->id,
                    'document_number' => $document->document_number ?: '—',
                    'document_type' => $document->document_type ?: '—',
                    'issue_date' => $document->issue_date,
                    'due_date' => $document->due_date,
                    'days_overdue' => (int) ($document->days_overdue ?? 0),
                    'original_amount' => (float) $document->original_amount,
                    'pending_amount' => (float) $document->pending_amount,
                    'status' => $document->status,
                    'management_count' => $logs->count(),
                    'last_management' => $this->managementSummary($latest),
                    'is_general' => false,
                ];
            })
            ->all();

        $generalLogs = $this->generalManagementRows(
            $advisorKey,
            $clientId,
            $periodFrom,
            $periodTo,
            $uen,
            $channel,
        );

        if ($generalLogs->isNotEmpty()) {
            array_unshift($rows, [
                'document_key' => 'general',
                'document_id' => null,
                'document_number' => 'Gestión general',
                'document_type' => 'Sin documento asociado',
                'issue_date' => null,
                'due_date' => null,
                'days_overdue' => 0,
                'original_amount' => 0.0,
                'pending_amount' => 0.0,
                'status' => 'management',
                'management_count' => $generalLogs->count(),
                'last_management' => $this->managementSummary($generalLogs->first()),
                'is_general' => true,
            ]);
        }

        return $rows;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function managementRows(
        string $advisorKey,
        int $clientId,
        string $documentKey,
        string $periodFrom = '',
        string $periodTo = '',
        string $uen = '',
        string $channel = '',
    ): array {
        $query = $this->managementBase($periodFrom, $periodTo, $uen, $channel)
            ->where('ml.client_id', $clientId);
        $this->applyAdvisor($query, $advisorKey, 'ml.advisor_id');

        if ($documentKey === 'general') {
            $query->whereNull('ml.portfolio_document_id');
        } else {
            $query->where('ml.portfolio_document_id', (int) $documentKey);
        }

        return $query
            ->select([
                'ml.id',
                'ml.type',
                'ml.subject',
                'ml.description',
                'ml.contact_date',
                'ml.contact_time',
                'ml.result',
                'ml.promised_date',
                'ml.promised_amount',
                'ml.status',
            ])
            ->orderByDesc('ml.contact_date')
            ->orderByDesc('ml.contact_time')
            ->orderByDesc('ml.id')
            ->get()
            ->map(fn ($row): array => [
                'id' => (int) $row->id,
                'type' => $this->managementTypeLabel($row->type),
                'subject' => $row->subject,
                'description' => $row->description,
                'contact_date' => $row->contact_date,
                'contact_time' => $row->contact_time ? substr((string) $row->contact_time, 0, 5) : null,
                'result' => $row->result,
                'promised_date' => $row->promised_date,
                'promised_amount' => $row->promised_amount !== null ? (float) $row->promised_amount : null,
                'status' => $row->status,
            ])
            ->all();
    }

    public function portfolioExportQuery(
        string $periodFrom = '',
        string $periodTo = '',
        string $uen = '',
        ?string $advisorKey = null,
        string $channel = '',
    ): Builder {
        $query = $this->portfolioBase($periodFrom, $periodTo, $uen, $channel)
            ->leftJoin('advisors as a', 'a.id', '=', 'pd.advisor_id')
            ->select([
                'pd.id',
                'pd.advisor_id',
                DB::raw('COALESCE(a.name, "Sin asignar") as advisor'),
                'c.name as client',
                'c.document_number as nit',
                'c.uen',
                'c.channel',
                'pd.document_number',
                'pd.document_type',
                'pd.issue_date',
                'pd.due_date',
                'pd.days_overdue',
                'pd.original_amount',
                'pd.pending_amount',
                'pd.status',
            ])
            ->orderBy('a.name')
            ->orderBy('c.name')
            ->orderBy('pd.document_number');

        if ($advisorKey !== null && $advisorKey !== '') {
            $this->applyAdvisor($query, $advisorKey, 'pd.advisor_id');
        }

        return $query;
    }

    /**
     * @return array<int, object>
     */
    public function clientExportRows(
        string $periodFrom = '',
        string $periodTo = '',
        string $uen = '',
        ?string $advisorKey = null,
        string $channel = '',
    ): array {
        $portfolioQuery = $this->portfolioBase($periodFrom, $periodTo, $uen, $channel)
            ->leftJoin('advisors as a', 'a.id', '=', 'pd.advisor_id');

        if ($advisorKey !== null && $advisorKey !== '') {
            $this->applyAdvisor($portfolioQuery, $advisorKey, 'pd.advisor_id');
        }

        $rows = $portfolioQuery
            ->select([
                'pd.advisor_id',
                DB::raw('COALESCE(a.name, "Sin asignar") as advisor'),
                'c.id as client_id',
                'c.name as client',
                'c.document_number as nit',
                'c.uen',
                'c.channel',
                DB::raw('COUNT(pd.id) as documentos'),
                DB::raw('SUM(pd.pending_amount) as saldo_total'),
                DB::raw('SUM(CASE WHEN pd.days_overdue > 0 THEN pd.pending_amount ELSE 0 END) as saldo_vencido'),
            ])
            ->groupBy(
                'pd.advisor_id',
                'a.name',
                'c.id',
                'c.name',
                'c.document_number',
                'c.uen',
                'c.channel',
            )
            ->orderBy('a.name')
            ->orderByDesc('saldo_total')
            ->get();

        $managementQuery = $this->managementBase($periodFrom, $periodTo, $uen, $channel);
        if ($advisorKey !== null && $advisorKey !== '') {
            $this->applyAdvisor($managementQuery, $advisorKey, 'ml.advisor_id');
        }
        $managementCounts = $managementQuery
            ->select([
                'ml.advisor_id',
                'ml.client_id',
                DB::raw('COUNT(*) as total'),
            ])
            ->groupBy('ml.advisor_id', 'ml.client_id')
            ->get()
            ->keyBy(fn ($row) => $this->advisorKey($row->advisor_id) . ':' . (int) $row->client_id);

        return $rows
            ->map(function ($row) use ($managementCounts): object {
                $key = $this->advisorKey($row->advisor_id) . ':' . (int) $row->client_id;
                $row->documentos = (int) $row->documentos;
                $row->gestiones = (int) ($managementCounts->get($key)?->total ?? 0);
                $row->saldo_total = (float) $row->saldo_total;
                $row->saldo_vencido = (float) $row->saldo_vencido;

                return $row;
            })
            ->all();
    }

    public function managementExportQuery(
        string $periodFrom = '',
        string $periodTo = '',
        string $uen = '',
        ?string $advisorKey = null,
        string $channel = '',
    ): Builder {
        $query = $this->managementBase($periodFrom, $periodTo, $uen, $channel)
            ->leftJoin('advisors as a', 'a.id', '=', 'ml.advisor_id')
            ->leftJoin('portfolio_documents as pd', 'pd.id', '=', 'ml.portfolio_document_id')
            ->select([
                'ml.id',
                'ml.advisor_id',
                DB::raw('COALESCE(a.name, "Sin asignar") as advisor'),
                'c.name as client',
                'c.document_number as nit',
                'c.uen',
                'c.channel',
                'pd.document_number',
                'ml.type',
                'ml.subject',
                'ml.description',
                'ml.contact_date',
                'ml.contact_time',
                'ml.result',
                'ml.promised_date',
                'ml.promised_amount',
                'ml.status',
            ])
            ->orderBy('a.name')
            ->orderBy('c.name')
            ->orderByDesc('ml.contact_date')
            ->orderByDesc('ml.id');

        if ($advisorKey !== null && $advisorKey !== '') {
            $this->applyAdvisor($query, $advisorKey, 'ml.advisor_id');
        }

        return $query;
    }

    private function portfolioBase(
        string $periodFrom,
        string $periodTo,
        string $uen,
        string $channel,
    ): Builder
    {
        $query = DB::table('portfolio_documents as pd')
            ->join('clients as c', 'c.id', '=', 'pd.client_id')
            ->whereNull('pd.deleted_at')
            ->whereIn('pd.status', PortfolioDocument::BALANCE_STATUSES);

        $loadId = $this->activePortfolioLoadId();
        if ($loadId === null) {
            $query->whereRaw('1 = 0');
        } else {
            $query->where('pd.portfolio_load_id', $loadId);
        }

        if ($periodFrom !== '') {
            $query->whereDate('pd.period_date', '>=', Carbon::parse($periodFrom . '-01')->startOfMonth());
        }
        if ($periodTo !== '') {
            $query->whereDate('pd.period_date', '<=', Carbon::parse($periodTo . '-01')->endOfMonth());
        }
        if ($uen !== '') {
            $query->where('c.uen', $uen);
        }
        if ($channel !== '') {
            $query->where('c.channel', $channel);
        }

        return $query;
    }

    private function managementBase(
        string $periodFrom,
        string $periodTo,
        string $uen,
        string $channel,
    ): Builder
    {
        $query = DB::table('management_logs as ml')
            ->join('clients as c', 'c.id', '=', 'ml.client_id')
            ->whereNull('ml.deleted_at');

        $loadId = $this->activePortfolioLoadId();
        if ($loadId === null) {
            $query->whereRaw('1 = 0');
        } else {
            $query->whereExists(function (Builder $portfolio) use ($loadId, $periodFrom, $periodTo): void {
                $portfolio
                    ->selectRaw('1')
                    ->from('portfolio_documents as report_pd')
                    ->whereColumn('report_pd.client_id', 'ml.client_id')
                    ->where('report_pd.portfolio_load_id', $loadId)
                    ->whereIn('report_pd.status', PortfolioDocument::BALANCE_STATUSES)
                    ->whereNull('report_pd.deleted_at')
                    ->where(function (Builder $advisor): void {
                        $advisor
                            ->whereColumn('report_pd.advisor_id', 'ml.advisor_id')
                            ->orWhere(function (Builder $unassigned): void {
                                $unassigned
                                    ->whereNull('report_pd.advisor_id')
                                    ->whereNull('ml.advisor_id');
                            });
                    });

                if ($periodFrom !== '') {
                    $portfolio->whereDate(
                        'report_pd.period_date',
                        '>=',
                        Carbon::parse($periodFrom . '-01')->startOfMonth(),
                    );
                }
                if ($periodTo !== '') {
                    $portfolio->whereDate(
                        'report_pd.period_date',
                        '<=',
                        Carbon::parse($periodTo . '-01')->endOfMonth(),
                    );
                }
            });
        }

        if ($periodFrom !== '') {
            $query->whereDate('ml.contact_date', '>=', Carbon::parse($periodFrom . '-01')->startOfMonth());
        }
        if ($periodTo !== '') {
            $query->whereDate('ml.contact_date', '<=', Carbon::parse($periodTo . '-01')->endOfMonth());
        }
        if ($uen !== '') {
            $query->where('c.uen', $uen);
        }
        if ($channel !== '') {
            $query->where('c.channel', $channel);
        }

        return $query;
    }

    /**
     * @param  int[]  $documentIds
     * @return Collection<int, Collection<int, object>>
     */
    private function managementRowsForDocuments(
        string $advisorKey,
        int $clientId,
        array $documentIds,
        string $periodFrom,
        string $periodTo,
        string $uen,
        string $channel,
    ): Collection {
        if ($documentIds === []) {
            return collect();
        }

        $query = $this->managementBase($periodFrom, $periodTo, $uen, $channel)
            ->where('ml.client_id', $clientId)
            ->whereIn('ml.portfolio_document_id', $documentIds)
            ->select([
                'ml.id',
                'ml.portfolio_document_id',
                'ml.type',
                'ml.subject',
                'ml.contact_date',
                'ml.contact_time',
            ])
            ->orderByDesc('ml.contact_date')
            ->orderByDesc('ml.contact_time')
            ->orderByDesc('ml.id');
        $this->applyAdvisor($query, $advisorKey, 'ml.advisor_id');

        return $query->get()->groupBy('portfolio_document_id');
    }

    /**
     * @return Collection<int, object>
     */
    private function generalManagementRows(
        string $advisorKey,
        int $clientId,
        string $periodFrom,
        string $periodTo,
        string $uen,
        string $channel,
    ): Collection {
        $query = $this->managementBase($periodFrom, $periodTo, $uen, $channel)
            ->where('ml.client_id', $clientId)
            ->whereNull('ml.portfolio_document_id')
            ->select(['ml.id', 'ml.type', 'ml.subject', 'ml.contact_date', 'ml.contact_time'])
            ->orderByDesc('ml.contact_date')
            ->orderByDesc('ml.contact_time')
            ->orderByDesc('ml.id');
        $this->applyAdvisor($query, $advisorKey, 'ml.advisor_id');

        return $query->get();
    }

    private function applyAdvisor(Builder $query, string $advisorKey, string $column): void
    {
        if ($advisorKey === 'unassigned') {
            $query->whereNull($column);

            return;
        }

        $query->where($column, (int) $advisorKey);
    }

    private function advisorKey(mixed $advisorId): string
    {
        return $advisorId === null ? 'unassigned' : (string) (int) $advisorId;
    }

    private function activePortfolioLoadId(): ?int
    {
        if (! $this->activeLoadResolved) {
            $id = DB::table('portfolio_loads')
                ->where('is_active', true)
                ->where('status', 'completed')
                ->orderByDesc('period_date')
                ->orderByDesc('version')
                ->orderByDesc('id')
                ->value('id');

            $this->activeLoadId = $id ? (int) $id : null;
            $this->activeLoadResolved = true;
        }

        return $this->activeLoadId;
    }

    private function managementSummary(?object $log): ?string
    {
        if ($log === null) {
            return null;
        }

        $date = $log->contact_date
            ? Carbon::parse($log->contact_date)->format('d/m/Y')
            : '—';

        return trim($date . ' · ' . $this->managementTypeLabel($log->type) . ' · ' . $log->subject);
    }

    public function managementTypeLabel(?string $type): string
    {
        return match ($type) {
            'call' => 'Llamada',
            'email' => 'Correo',
            'visit' => 'Visita',
            'agreement' => 'Acuerdo',
            'legal' => 'Jurídico',
            default => 'Otro',
        };
    }
}
