<?php

namespace App\Filament\Resources\CollectionLoadResource\Pages;

use App\Filament\Resources\CollectionLoadResource;
use App\Models\CollectionLoad;
use App\Services\Loads\CollectionLoadService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Schema as DbSchema;
use Livewire\WithFileUploads;

class ListCollectionLoads extends ListRecords
{
    use WithFileUploads;

    protected static string $resource = CollectionLoadResource::class;

    public $uploadFile = null;

    public ?string $uploadNotes = null;

    public ?array $lastResult = null;

    public function getTitle(): string
    {
        return 'Carga de recaudos';
    }

    public function table(Table $table): Table
    {
        return CollectionLoadResource::table($table)
            ->heading('Historial reciente')
            ->paginated([10, 25, 50]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            View::make('filament.resources.collection-load-resource.pages.list-collection-loads')
                ->viewData([
                    'page' => $this,
                ]),
            EmbeddedTable::make(),
        ]);
    }

    public function submitUpload(CollectionLoadService $service): void
    {
        $this->validate([
            'uploadFile'  => ['required', 'file', 'mimes:csv,xlsx', 'max:51200'],
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

            $this->lastResult = [
                'load_id'   => $result->loadId,
                'reference' => $result->reference,
                'status'    => $result->status,
            ];

            $this->reset('uploadFile', 'uploadNotes');

            $notification = Notification::make()
                ->title(match ($result->status) {
                    'completed' => 'Carga de recaudos completada',
                    'rejected'  => 'Carga de recaudos rechazada',
                    'failed'    => 'Carga de recaudos fallida',
                    default     => 'Carga de recaudos registrada',
                })
                ->body("Referencia {$result->reference} — filas válidas: {$result->validRows}.");

            match ($result->status) {
                'completed' => $notification->success(),
                'rejected', 'failed' => $notification->danger(),
                default => $notification->info(),
            };

            $notification->send();
        } catch (\Throwable $exception) {
            Notification::make()
                ->title('No fue posible registrar la carga de recaudos')
                ->body($exception->getMessage())
                ->danger()
                ->send();
        }
    }

    public function summaryCards(): array
    {
        $loads = CollectionLoad::query();
        $latestSuccess = $this->latestSuccessfulLoad();
        $completedLoads = CollectionLoad::query()->where('status', 'completed');

        return [
            'total_loads' => (int) $loads->count(),
            'latest_success_at' => $latestSuccess?->processed_at,
            'latest_success_label' => $this->formatLatestSuccessLabel($latestSuccess),
            'details_loaded' => (int) $completedLoads->sum($this->hasLoadColumn('detail_count') ? 'detail_count' : 'processed_rows'),
            'historical_amount' => (float) (
                $this->hasLoadColumn('total_collected')
                    ? CollectionLoad::query()->where('status', 'completed')->sum('total_collected')
                    : 0
            ),
        ];
    }

    public function latestSuccessfulLoad(): ?CollectionLoad
    {
        $query = CollectionLoad::query()->where('status', 'completed');

        if ($this->hasLoadColumn('period_date')) {
            $query->orderByDesc('period_date');
        }

        if ($this->hasLoadColumn('version')) {
            $query->orderByDesc('version');
        }

        if ($this->hasLoadColumn('processed_at')) {
            $query->orderByDesc('processed_at');
        }

        return $query
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->first();
    }

    public function latestSuccessfulLoadUrl(): ?string
    {
        $record = $this->latestSuccessfulLoad();

        return $record ? CollectionLoadResource::getUrl('view', ['record' => $record]) : null;
    }

    public function lastResultUrl(): ?string
    {
        return isset($this->lastResult['load_id'])
            ? CollectionLoadResource::getUrl('view', ['record' => $this->lastResult['load_id']])
            : null;
    }

    protected function formatLatestSuccessLabel(?CollectionLoad $load): string
    {
        if (! $load) {
            return 'Sin cargas exitosas';
        }

        if ($this->hasLoadColumn('period_key') && filled($load->period_key)) {
            return $this->hasLoadColumn('version') && filled($load->version)
                ? "{$load->period_key} · v{$load->version}"
                : $load->period_key;
        }

        return $load->reference ?: "Carga #{$load->getKey()}";
    }

    protected function hasLoadColumn(string $column): bool
    {
        static $cache = [];

        return $cache[$column] ??= DbSchema::hasColumn((new CollectionLoad())->getTable(), $column);
    }
}
