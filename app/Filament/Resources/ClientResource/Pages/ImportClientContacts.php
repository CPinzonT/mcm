<?php

namespace App\Filament\Resources\ClientResource\Pages;

use App\Filament\Resources\ClientResource;
use App\Models\Client;
use App\Models\ClientContactLoad;
use App\Services\Clients\ClientContactImportService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Livewire\WithFileUploads;

class ImportClientContacts extends Page
{
    use WithFileUploads;

    protected static string $resource = ClientResource::class;

    protected static ?string $title = 'Actualización data';

    protected static ?string $navigationLabel = 'Actualización data';

    public $uploadFile = null;

    public ?array $lastResult = null;

    public function getTitle(): string
    {
        return 'Actualización data';
    }

    public static function canAccess(array $parameters = []): bool
    {
        $user = auth()->user();

        return $user?->hasAnyRole(['admin', 'analyst', 'analista', 'coordinator']) ?? false;
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            View::make('filament.resources.client-resource.pages.import-client-contacts')
                ->viewData([
                    'page' => $this,
                ]),
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('volver')
                ->label('Volver a clientes')
                ->icon('heroicon-o-arrow-left')
                ->url(ClientResource::getUrl('index')),
        ];
    }

    public function submitUpload(ClientContactImportService $service): void
    {
        $this->validate([
            'uploadFile' => ['required', 'file', 'mimes:csv,xlsx,xls', 'max:51200'],
        ]);

        try {
            @ini_set('max_execution_time', '3600');
            @ini_set('memory_limit', '1024M');

            $result = $service->handleUpload(
                uploadedFile: $this->uploadFile,
                user: auth()->user(),
            );

            $load = $result['load'];

            $this->lastResult = [
                'reference' => $load->reference,
                'status' => $load->status,
                'updated_rows' => $result['updated_rows'],
                'not_found_rows' => $result['not_found_rows'],
                'error_rows' => $result['error_rows'],
                'skipped_rows' => $result['skipped_rows'],
                'error_preview' => $result['error_preview'],
            ];

            $this->reset('uploadFile');

            $notification = Notification::make()
                ->title(match ($load->status) {
                    'completed' => 'Contactos actualizados',
                    'rejected' => 'Actualización rechazada',
                    default => 'Actualización registrada',
                })
                ->body("Referencia {$load->reference} — clientes actualizados: {$result['updated_rows']}.");

            match ($load->status) {
                'completed' => $notification->success(),
                'rejected' => $notification->danger(),
                default => $notification->info(),
            };

            $notification->send();
        } catch (\Throwable $exception) {
            Notification::make()
                ->title('No fue posible procesar el archivo maestro')
                ->body($exception->getMessage())
                ->danger()
                ->send();
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function summaryCards(): array
    {
        $loads = ClientContactLoad::query();
        $latestSuccess = ClientContactLoad::query()
            ->where('status', 'completed')
            ->orderByDesc('processed_at')
            ->first();

        return [
            'total_loads' => (int) $loads->count(),
            'clients_total' => (int) Client::query()->count(),
            'latest_success_at' => $latestSuccess?->processed_at,
            'latest_success_label' => $latestSuccess?->reference ?: 'Sin cargas exitosas',
            'rows_updated' => (int) ClientContactLoad::query()->where('status', 'completed')->sum('updated_rows'),
        ];
    }

    /**
     * @return \Illuminate\Support\Collection<int, ClientContactLoad>
     */
    public function recentLoads()
    {
        return ClientContactLoad::query()
            ->with('uploadedBy')
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();
    }
}
