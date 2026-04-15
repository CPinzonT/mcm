<?php

namespace App\Filament\Resources\PortfolioDocumentResource\Pages;

use App\Filament\Resources\PortfolioDocumentResource;
use App\Models\ManagementLog;
use App\Models\PortfolioDocument;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;

class ViewPortfolioDocument extends ViewRecord
{
    protected static string $resource = PortfolioDocumentResource::class;

    protected string $view = 'filament.resources.portfolio-document-resource.pages.view-portfolio-document';

    // Management log form fields
    public string $mgType        = 'call';
    public string $mgSubject     = '';
    public string $mgDescription = '';
    public string $mgResult      = '';
    public string $mgContactDate = '';
    public string $mgFollowUp    = '';
    public string $mgPromisedAmt = '';
    public string $mgPromisedDate = '';

    protected function getHeaderActions(): array
    {
        return [EditAction::make()];
    }

    public function mount(int|string $record): void
    {
        parent::mount($record);
        $this->mgContactDate = now()->format('Y-m-d');
    }

    public function saveManagement(): void
    {
        $this->validate([
            'mgType'        => 'required|in:call,email,visit,agreement,legal,other',
            'mgSubject'     => 'required|min:3|max:255',
            'mgDescription' => 'required|min:5',
            'mgResult'      => 'nullable|in:no_contact,promise_to_pay,partial_payment,refused,arrangement,other',
            'mgContactDate' => 'required|date',
            'mgFollowUp'    => 'nullable|date',
            'mgPromisedAmt' => 'nullable|numeric|min:0',
            'mgPromisedDate'=> 'nullable|date',
        ]);

        ManagementLog::create([
            'client_id'             => $this->record->client_id,
            'portfolio_document_id' => $this->record->id,
            'advisor_id'            => $this->record->advisor_id,
            'user_id'               => Auth::id(),
            'type'                  => $this->mgType,
            'subject'               => $this->mgSubject,
            'description'           => $this->mgDescription,
            'result'                => $this->mgResult ?: null,
            'contact_date'          => $this->mgContactDate,
            'follow_up_date'        => $this->mgFollowUp ?: null,
            'promised_amount'       => $this->mgPromisedAmt !== '' ? (float) $this->mgPromisedAmt : null,
            'promised_date'         => $this->mgPromisedDate ?: null,
            'status'                => 'open',
        ]);

        $this->mgSubject      = '';
        $this->mgDescription  = '';
        $this->mgResult       = '';
        $this->mgFollowUp     = '';
        $this->mgPromisedAmt  = '';
        $this->mgPromisedDate = '';
        $this->mgContactDate  = now()->format('Y-m-d');

        unset($this->managementLogs);

        $this->dispatch('notify', ['type' => 'success', 'message' => 'Gestión registrada correctamente.']);
    }

    public function appendQuickReply(string $text): void
    {
        $this->mgDescription = trim($this->mgDescription)
            ? $this->mgDescription . "\n" . $text
            : $text;
    }

    #[Computed]
    public function managementLogs(): array
    {
        return ManagementLog::where('portfolio_document_id', $this->record->id)
            ->with(['advisor', 'user'])
            ->orderByDesc('contact_date')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->map(fn (ManagementLog $log) => [
                'id'            => $log->id,
                'date'          => $log->contact_date?->format('d/m/Y'),
                'type'          => $log->type,
                'type_label'    => $log->getTypeLabel(),
                'subject'       => $log->subject,
                'description'   => $log->description,
                'result'        => $log->result,
                'result_label'  => match($log->result) {
                    'no_contact'      => 'Sin Contacto',
                    'promise_to_pay'  => 'Promesa de Pago',
                    'partial_payment' => 'Pago Parcial',
                    'refused'         => 'Rechazó',
                    'arrangement'     => 'Acuerdo',
                    default           => 'Otro',
                },
                'advisor'       => $log->advisor?->name ?? $log->user?->name ?? '—',
                'follow_up'     => $log->follow_up_date?->format('d/m/Y'),
                'promised_amt'  => $log->promised_amount ? number_format((float) $log->promised_amount, 0, ',', '.') : null,
                'promised_date' => $log->promised_date?->format('d/m/Y'),
                'status'        => $log->status,
            ])
            ->toArray();
    }

    #[Computed]
    public function clientDocuments(): array
    {
        return PortfolioDocument::where('client_id', $this->record->client_id)
            ->where('id', '!=', $this->record->id)
            ->orderByDesc('days_overdue')
            ->limit(10)
            ->get(['id', 'document_number', 'document_type', 'pending_amount', 'days_overdue', 'risk_level'])
            ->map(fn (PortfolioDocument $d) => [
                'id'              => $d->id,
                'number'          => $d->document_number,
                'type'            => $d->document_type,
                'pending_amount'  => number_format((float) $d->pending_amount, 0, ',', '.'),
                'days_overdue'    => $d->days_overdue,
                'risk_level'      => $d->risk_level,
            ])
            ->toArray();
    }
}
