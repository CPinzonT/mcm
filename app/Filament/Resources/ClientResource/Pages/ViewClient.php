<?php

namespace App\Filament\Resources\ClientResource\Pages;

use App\Filament\Resources\ClientResource;
use App\Models\ManagementLog;
use App\Models\PortfolioDocument;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\View\View;
use Livewire\WithPagination;

class ViewClient extends ViewRecord
{
    use WithPagination;

    protected static string $resource = ClientResource::class;

    protected string $view = 'filament.resources.client-resource.pages.view-client';

    public string $contactName = '';
    public string $contactEmail = '';
    public string $contactPhone = '';

    protected function getHeaderActions(): array
    {
        return [EditAction::make()];
    }

    public function mount(int|string $record): void
    {
        parent::mount($record);

        $this->contactName = $this->record->contact_name ?? '';
        $this->contactEmail = $this->record->contact_email ?? '';
        $this->contactPhone = $this->record->contact_phone ?? '';
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

    public function getDocumentsProperty()
    {
        return PortfolioDocument::where('client_id', $this->record->id)
            ->orderByDesc('days_overdue')
            ->paginate(10, pageName: 'docs-page');
    }

    public function getPortfolioSummaryProperty(): array
    {
        $docs = PortfolioDocument::where('client_id', $this->record->id);

        return [
            'total_balance'   => (clone $docs)->sum('pending_amount'),
            'overdue_balance' => (clone $docs)->where('days_overdue', '>', 0)->sum('pending_amount'),
            'total_docs'      => (clone $docs)->count(),
            'overdue_docs'    => (clone $docs)->where('days_overdue', '>', 0)->count(),
            'max_overdue'     => (clone $docs)->max('days_overdue') ?? 0,
        ];
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
                'date'        => $log->contact_date?->format('d/m/Y'),
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
