<?php

namespace App\Filament\Resources\ClientResource\Pages;

use App\Filament\Resources\ClientResource;
use App\Models\CollectionDetail;
use App\Models\ManagementLog;
use App\Models\PortfolioDocument;
use App\Services\Management\ManagementLogWriter;
use App\Services\Risk\RiskClassificationService;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Livewire\WithPagination;

class ViewClient extends ViewRecord
{
    use WithPagination;

    protected static string $resource = ClientResource::class;

    protected string $view = 'filament.resources.client-resource.pages.view-client';

    public string $contactName = '';
    public string $contactEmail = '';
    public string $contactPhone = '';

    public bool $showMgmtModal = false;
    public ?int $mgmtDocId = null;
    public string $mgmtDocLabel = '';
    public string $mgType = 'agreement';
    public string $mgSubject = '';
    public string $mgDescription = '';
    public string $mgResult = '';
    public string $mgContactDate = '';
    public string $mgContactTime = '';
    public string $mgFollowUp = '';
    public string $mgPromisedAmt = '';
    public string $mgPromisedDate = '';

    /** Búsqueda rápida en documentos de cartera (número, referencia, tipo). */
    public string $docSearch = '';

    public function exportPortfolioUrl(): ?string
    {
        $period = $this->latestPortfolioCutDate();

        if (! $period) {
            return null;
        }

        return route('admin.exports.portfolio', [
            'period'    => substr((string) $period, 0, 7),
            'client_id' => $this->record->id,
        ]);
    }

    /** Carga de cartera más reciente (misma regla que el dashboard). */
    private function latestPortfolioLoadId(): ?int
    {
        return DB::table('portfolio_loads')
            ->where('is_active', true)
            ->where('status', 'completed')
            ->orderByDesc('period_date')
            ->orderByDesc('version')
            ->value('id');
    }

    /**
     * Corte de la carga más reciente; solo documentos operativos en KPIs y listado.
     */
    private function latestPortfolioCutDate(): ?string
    {
        $loadId = $this->latestPortfolioLoadId();
        if (! $loadId) {
            return null;
        }

        return DB::table('portfolio_loads')->where('id', $loadId)->value('period_date');
    }

    /** Fecha de consulta para mora en ficha cliente (siempre hoy). */
    private function portfolioConsultationDate(): string
    {
        return Carbon::today()->format('Y-m-d');
    }

    private function liveDaysOverdueExpression(): string
    {
        return 'GREATEST(0, DATEDIFF(?, DATE(due_date)))';
    }

    /** Cartera del último corte: solo documentos operativos (sin expresión de mora en el SELECT). */
    private function activePortfolioDocumentsBaseQuery(): Builder
    {
        $loadId = $this->latestPortfolioLoadId();

        $query = PortfolioDocument::query()
            ->whereIn('status', PortfolioDocument::BALANCE_STATUSES)
            ->where('client_id', $this->record->id)
            ->whereNull('deleted_at')
            ->whereHas('portfolioLoad', function ($q) use ($loadId) {
                $q->where('status', 'completed');
                if ($loadId) {
                    $q->where('id', $loadId);
                } else {
                    $q->whereRaw('1 = 0');
                }
            });

        $cut = $this->latestPortfolioCutDate();
        if ($cut) {
            $query->whereDate('period_date', $cut);
        }

        return $query;
    }

    private function activePortfolioDocumentsQuery(): Builder
    {
        $asOf = $this->portfolioConsultationDate();

        return $this->activePortfolioDocumentsBaseQuery()
            ->selectRaw(
                'portfolio_documents.*, ' . $this->liveDaysOverdueExpression() . ' as live_days_overdue',
                [$asOf]
            );
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportPortfolio')
                ->label('Exportar cartera')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->url(fn (): string => $this->exportPortfolioUrl() ?? '#')
                ->openUrlInNewTab()
                ->visible(fn (): bool => $this->exportPortfolioUrl() !== null),
            EditAction::make(),
        ];
    }

    public function mount(int|string $record): void
    {
        parent::mount($record);

        $this->contactName = $this->record->contact_name ?? '';
        $this->contactEmail = $this->record->contact_email ?? '';
        $this->contactPhone = $this->record->contact_phone ?? '';
        $this->mgContactDate = now()->format('Y-m-d');
        $this->mgContactTime = now()->format('H:i');
    }

    public function openManagementModal(int $documentId): void
    {
        $doc = PortfolioDocument::query()
            ->where('client_id', $this->record->id)
            ->findOrFail($documentId);

        $this->mgmtDocId = $doc->id;
        $this->mgmtDocLabel = trim(($doc->document_type ?? 'Doc') . ' #' . $doc->document_number);
        $this->showMgmtModal = true;
        $this->resetManagementForm(false);
    }

    public function closeManagementModal(): void
    {
        $this->showMgmtModal = false;
        $this->mgmtDocId = null;
        $this->mgmtDocLabel = '';
    }

    public function saveManagement(): void
    {
        if (! $this->mgmtDocId) {
            return;
        }

        $this->validate([
            'mgmtDocId'       => 'required|integer',
            'mgType'          => 'required|in:call,email,visit,agreement,legal,other',
            'mgSubject'       => 'required|min:3|max:255',
            'mgDescription'   => 'required|min:5',
            'mgResult'        => 'nullable|in:no_contact,promise_to_pay,partial_payment,refused,arrangement,other',
            'mgContactDate'   => 'required|date',
            'mgContactTime'   => 'required|date_format:H:i',
            'mgFollowUp'      => 'nullable|date',
            'mgPromisedAmt'   => 'nullable|numeric|min:0',
            'mgPromisedDate'  => 'nullable|date',
        ]);

        $doc = PortfolioDocument::query()
            ->where('client_id', $this->record->id)
            ->findOrFail($this->mgmtDocId);

        ManagementLogWriter::createForDocument($doc, [
            'type'            => $this->mgType,
            'subject'         => $this->mgSubject,
            'description'     => $this->mgDescription,
            'result'          => $this->mgResult ?: null,
            'contact_date'    => $this->mgContactDate,
            'contact_time'    => $this->mgContactTime,
            'follow_up_date'  => $this->mgFollowUp ?: null,
            'promised_amount' => $this->mgPromisedAmt !== '' ? (float) $this->mgPromisedAmt : null,
            'promised_date'   => $this->mgPromisedDate ?: null,
        ]);

        $this->closeManagementModal();
        $this->resetManagementForm();

        $this->dispatch('notify', [
            'type'    => 'success',
            'message' => 'Gestión registrada correctamente.',
        ]);
    }

    public function appendQuickReply(string $text): void
    {
        $this->mgDescription = trim($this->mgDescription)
            ? $this->mgDescription . "\n" . $text
            : $text;
    }

    private function resetManagementForm(bool $resetDoc = true): void
    {
        if ($resetDoc) {
            $this->mgmtDocId = null;
            $this->mgmtDocLabel = '';
        }

        $this->mgType = 'agreement';
        $this->mgSubject = '';
        $this->mgDescription = '';
        $this->mgResult = '';
        $this->mgFollowUp = '';
        $this->mgPromisedAmt = '';
        $this->mgPromisedDate = '';
        $this->mgContactDate = now()->format('Y-m-d');
        $this->mgContactTime = now()->format('H:i');
    }

    public function saveContact(): void
    {
        $this->record->update([
            'contact_name'  => $this->contactName,
            'contact_email' => $this->contactEmail,
            'contact_phone' => $this->contactPhone,
        ]);

        $this->dispatch('notify', [
            'type'    => 'success',
            'message' => 'Contacto actualizado correctamente.',
        ]);
    }

    public function updatedDocSearch(): void
    {
        $this->resetPage('docs-page');
    }

    public function clearDocSearch(): void
    {
        $this->docSearch = '';
        $this->resetPage('docs-page');
    }

    public function getDocumentsProperty()
    {
        $asOf = $this->portfolioConsultationDate();

        $query = $this->activePortfolioDocumentsQuery();

        $term = trim($this->docSearch);
        if ($term !== '') {
            $like = '%' . addcslashes($term, '%_\\') . '%';
            $query->where(function (Builder $q) use ($like) {
                $q->where('document_number', 'like', $like)
                    ->orWhere('client_reference', 'like', $like)
                    ->orWhere('document_type', 'like', $like);
            });
        }

        return $query
            ->orderByRaw($this->liveDaysOverdueExpression() . ' DESC', [$asOf])
            ->paginate(10, pageName: 'docs-page');
    }

    public function getPortfolioSummaryProperty(): array
    {
        $asOf = $this->portfolioConsultationDate();
        $docs = $this->activePortfolioDocumentsBaseQuery();
        $overdueClause = $this->liveDaysOverdueExpression() . ' > 0';
        $operative = PortfolioDocument::OPERATIVE_STATUSES;

        return [
            'total_balance'   => (float) (clone $docs)->sum('pending_amount'),
            'overdue_balance' => (float) (clone $docs)->whereIn('status', $operative)->whereRaw($overdueClause, [$asOf])->sum('pending_amount'),
            'total_docs'      => (clone $docs)->count(),
            'overdue_docs'    => (clone $docs)->whereIn('status', $operative)->whereRaw($overdueClause, [$asOf])->count(),
            'max_overdue'     => (int) ((clone $docs)
                ->whereIn('status', $operative)
                ->selectRaw('MAX(' . $this->liveDaysOverdueExpression() . ') as max_live', [$asOf])
                ->value('max_live') ?? 0),
            'cut_date'        => $this->latestPortfolioCutDate(),
            'consultation_date' => $asOf,
        ];
    }

    public function liveRiskLevelForDocument(PortfolioDocument $doc): string
    {
        $days = (int) ($doc->live_days_overdue ?? 0);

        return app(RiskClassificationService::class)->riskLevelForDays($days);
    }

    public function getClientCommercialKpisProperty(): array
    {
        $client = $this->record;
        $clientId = $client->id;

        $docBase = $this->activePortfolioDocumentsBaseQuery();

        $cartera = (float) (clone $docBase)->sum('pending_amount');
        $cupoDocumentado = (float) (clone $docBase)->sum('original_amount');

        $latestCollectionLoadId = DB::table('collection_loads')
            ->where('is_active', true)
            ->where('status', 'completed')
            ->orderByDesc('processed_at')
            ->orderByDesc('version')
            ->orderByDesc('id')
            ->value('id');

        $recaudoQuery = CollectionDetail::query()->where('client_id', $clientId);

        if ($latestCollectionLoadId) {
            $recaudoQuery->where('collection_load_id', (int) $latestCollectionLoadId);
        } else {
            $recaudoQuery->whereRaw('1 = 0');
        }

        $recaudo = (float) $recaudoQuery->sum('amount');

        $rotationDays = ($recaudo > 0 && $cartera > 0)
            ? round($cartera / ($recaudo / 30), 1)
            : null;

        $plazoDays = $client->payment_term_days;
        if ($plazoDays === null) {
            $plazoDays = $this->inferPaymentTermDays($docBase);
        }

        $cupo = $client->credit_limit !== null
            ? (float) $client->credit_limit
            : $cupoDocumentado;

        return [
            'cupo'              => $cupo,
            'cupo_es_maestro'   => $client->credit_limit !== null,
            'cartera_pendiente' => $cartera,
            'rotation_days'     => $rotationDays,
            'plazo_days'        => $plazoDays,
            'recaudo_mes'       => $recaudo,
        ];
    }

    private function inferPaymentTermDays($docQuery): ?int
    {
        $terms = (clone $docQuery)
            ->whereNotNull('issue_date')
            ->whereNotNull('due_date')
            ->get(['issue_date', 'due_date'])
            ->map(fn (PortfolioDocument $doc) => max(0, (int) $doc->issue_date->diffInDays($doc->due_date)))
            ->filter(fn (int $days) => $days > 0)
            ->values();

        if ($terms->isEmpty()) {
            return null;
        }

        return (int) round($terms->median());
    }

    public function getTimelineProperty(): array
    {
        return ManagementLog::where('client_id', $this->record->id)
            ->with(['advisor', 'portfolioDocument'])
            ->orderByDesc('contact_date')
            ->orderByDesc('created_at')
            ->limit(30)
            ->get()
            ->map(fn (ManagementLog $log) => [
                'date'        => $log->contactDateTimeLabel(),
                'type'        => $log->getTypeLabel(),
                'type_key'    => $log->type,
                'subject'     => $log->subject,
                'description' => $log->description,
                'result'      => $log->result,
                'advisor'     => $log->advisor?->name ?? '—',
                'doc_number'  => $log->portfolioDocument?->document_number,
                'promised'    => $log->promised_amount ? '$' . number_format($log->promised_amount, 0, ',', '.') : null,
                'promised_date' => $log->promised_date?->format('d/m/Y'),
            ])
            ->toArray();
    }
}
