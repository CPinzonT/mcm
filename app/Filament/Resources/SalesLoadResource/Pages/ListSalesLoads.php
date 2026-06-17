<?php

namespace App\Filament\Resources\SalesLoadResource\Pages;

use App\Filament\Resources\SalesLoadResource;
use App\Models\SalesLoad;
use App\Services\Loads\SalesLoadService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Schema as DbSchema;
use Livewire\WithFileUploads;

class ListSalesLoads extends ListRecords
{
    use WithFileUploads;

    protected static string $resource = SalesLoadResource::class;

    public ?string $azureUrl = null;

    public $uploadFile = null;

    public ?string $uploadNotes = null;

    public ?array $lastResult = null;

    public function mount(): void
    {
        parent::mount();

        $this->azureUrl = config('mcm.sales.adls_default_url') ?: null;
    }

    public function getTitle(): string
    {
        return 'Carga de ventas';
    }

    public function table(Table $table): Table
    {
        return SalesLoadResource::table($table)
            ->heading('Historial reciente')
            ->paginated([10, 25, 50]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            View::make('filament.resources.sales-load-resource.pages.list-sales-loads')
                ->viewData([
                    'page' => $this,
                ]),
            EmbeddedTable::make(),
        ]);
    }

    public function submitFromAzure(SalesLoadService $service): void
    {
        $this->validate([
            'azureUrl' => ['required', 'url', 'max:2048'],
            'uploadNotes' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            @ini_set('max_execution_time', '3600');
            @ini_set('memory_limit', '1024M');

            $result = $service->handleFromUrl(
                url: $this->azureUrl,
                notes: $this->uploadNotes,
                user: auth()->user(),
            );

            $this->lastResult = $this->resultPayload($result);
            $this->reset('uploadNotes');

            $this->notifyResult($result->status, $result->reference, $result->validRows);
        } catch (\Throwable $exception) {
            Notification::make()
                ->title('No fue posible importar ventas desde Azure')
                ->body($exception->getMessage())
                ->danger()
                ->send();
        }
    }

    public function submitUpload(SalesLoadService $service): void
    {
        $this->validate([
            'uploadFile' => ['required', 'file', 'mimes:csv,xlsx,xls', 'max:51200'],
            'uploadNotes' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            @ini_set('max_execution_time', '3600');
            @ini_set('memory_limit', '1024M');

            $result = $service->handleUpload(
                uploadedFile: $this->uploadFile,
                notes: $this->uploadNotes,
                user: auth()->user(),
            );

            $this->lastResult = $this->resultPayload($result);
            $this->reset('uploadFile', 'uploadNotes');

            $this->notifyResult($result->status, $result->reference, $result->validRows);
        } catch (\Throwable $exception) {
            Notification::make()
                ->title('No fue posible registrar la carga de ventas')
                ->body($exception->getMessage())
                ->danger()
                ->send();
        }
    }

    public function summaryCards(): array
    {
        $loads = SalesLoad::query();
        $latestSuccess = $this->latestSuccessfulLoad();
        $completedLoads = SalesLoad::query()->where('status', 'completed');

        return [
            'total_loads' => (int) $loads->count(),
            'latest_success_at' => $latestSuccess?->processed_at,
            'latest_success_label' => $this->formatLatestSuccessLabel($latestSuccess),
            'rows_loaded' => (int) $completedLoads->sum('processed_rows'),
            'historical_amount' => (float) $completedLoads->sum('total_amount'),
        ];
    }

    public function latestSuccessfulLoad(): ?SalesLoad
    {
        return SalesLoad::query()
            ->where('status', 'completed')
            ->orderByDesc('processed_at')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->first();
    }

    public function latestSuccessfulLoadUrl(): ?string
    {
        $record = $this->latestSuccessfulLoad();

        return $record ? SalesLoadResource::getUrl('view', ['record' => $record]) : null;
    }

    public function lastResultUrl(): ?string
    {
        return isset($this->lastResult['load_id'])
            ? SalesLoadResource::getUrl('view', ['record' => $this->lastResult['load_id']])
            : null;
    }

    protected function formatLatestSuccessLabel(?SalesLoad $load): string
    {
        if (! $load) {
            return 'Sin cargas exitosas';
        }

        if ($this->hasLoadColumn('period_key') && filled($load->period_key)) {
            return $load->period_key;
        }

        return $load->reference ?: "Carga #{$load->getKey()}";
    }

    protected function hasLoadColumn(string $column): bool
    {
        static $cache = [];

        return $cache[$column] ??= DbSchema::hasColumn((new SalesLoad())->getTable(), $column);
    }

    /**
     * @return array<string, mixed>
     */
    private function resultPayload(\App\Data\Loads\LoadProcessingResultData $result): array
    {
        return [
            'load_id' => $result->loadId,
            'reference' => $result->reference,
            'status' => $result->status,
            'period_key' => $result->periodKey,
            'total_rows' => $result->totalRows,
            'processed_rows' => $result->processedRows,
            'error_rows' => $result->errorRows,
            'total_amount' => $result->totalAmount,
            'error_preview' => $result->errorPreview,
        ];
    }

    private function notifyResult(string $status, string $reference, int $validRows): void
    {
        $notification = Notification::make()
            ->title(match ($status) {
                'completed' => 'Carga de ventas completada',
                'rejected' => 'Carga de ventas rechazada',
                'failed' => 'Carga de ventas fallida',
                default => 'Carga de ventas registrada',
            })
            ->body("Referencia {$reference} — filas válidas: {$validRows}.");

        match ($status) {
            'completed' => $notification->success(),
            'rejected', 'failed' => $notification->danger(),
            default => $notification->info(),
        };

        $notification->send();
    }
}
