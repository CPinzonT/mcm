<?php

namespace App\Filament\Resources\PortfolioLoadResource\Pages;

use App\Filament\Resources\PortfolioLoadResource;
use App\Models\PortfolioLoad;
use App\Services\Loads\PortfolioLoadService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Schema as DbSchema;
use Livewire\WithFileUploads;

class ListPortfolioLoads extends ListRecords
{
    use WithFileUploads;

    protected static string $resource = PortfolioLoadResource::class;

    public $uploadFile = null;

    public ?string $uploadPeriodKey = null;

    public ?string $uploadNotes = null;

    public ?array $lastResult = null;

    public function getTitle(): string
    {
        return 'Carga de cartera';
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Historial reciente')
            ->paginated([10, 25, 50]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            View::make('filament.resources.portfolio-load-resource.pages.list-portfolio-loads')
                ->viewData([
                    'page' => $this,
                ]),
            EmbeddedTable::make(),
        ]);
    }

    public function submitUpload(PortfolioLoadService $service): void
    {
        $this->validate([
            'uploadFile' => ['required', 'file', 'mimes:csv,xlsx,xls', 'max:51200'],
            'uploadPeriodKey' => ['nullable', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/'],
            'uploadNotes' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $result = $service->handleUpload(
                uploadedFile: $this->uploadFile,
                notes: $this->uploadNotes,
                user: auth()->user(),
                forcedPeriodKey: $this->uploadPeriodKey,
            );

            $this->lastResult = [
                'load_id' => $result->loadId,
                'reference' => $result->reference,
                'status' => $result->status,
                'period_key' => $result->periodKey,
                'version' => $result->version,
                'total_rows' => $result->totalRows,
                'valid_rows' => $result->validRows,
                'processed_rows' => $result->processedRows,
                'error_rows' => $result->errorRows,
                'empty_rows' => $result->emptyRows,
                'duplicate_rows' => $result->duplicateRows,
                'total_amount' => $result->totalAmount,
                'item_count' => $result->itemCount,
                'error_preview' => $result->errorPreview,
                'summary' => $result->summary,
            ];

            $this->reset('uploadFile', 'uploadPeriodKey', 'uploadNotes');

            Notification::make()
                ->title($result->status === 'completed' ? 'Carga de cartera completada' : 'Carga de cartera rechazada')
                ->body($result->status === 'completed'
                    ? "Periodo {$result->periodKey} · version {$result->version}."
                    : 'Revisa el resumen y descarga el reporte de errores para corregir el archivo.')
                ->{$result->status === 'completed' ? 'success' : 'danger'}()
                ->send();
        } catch (\Throwable $exception) {
            Notification::make()
                ->title('No fue posible procesar la carga de cartera')
                ->body($exception->getMessage())
                ->danger()
                ->send();
        }
    }

    public function summaryCards(): array
    {
        $loads = PortfolioLoad::query();
        $latestSuccess = $this->latestSuccessfulLoad();
        $completedLoads = PortfolioLoad::query()->where('status', 'completed');

        return [
            'total_loads' => (int) $loads->count(),
            'latest_success_at' => $latestSuccess?->processed_at,
            'latest_success_label' => $this->formatLatestSuccessLabel($latestSuccess),
            'documents_loaded' => (int) $completedLoads->sum($this->hasLoadColumn('document_count') ? 'document_count' : 'processed_rows'),
            'historical_amount' => (float) (
                $this->hasLoadColumn('total_pending_amount')
                    ? PortfolioLoad::query()->where('status', 'completed')->sum('total_pending_amount')
                    : 0
            ),
        ];
    }

    public function latestSuccessfulLoad(): ?PortfolioLoad
    {
        $query = PortfolioLoad::query()->where('status', 'completed');

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

        return $record ? PortfolioLoadResource::getUrl('view', ['record' => $record]) : null;
    }

    public function lastResultUrl(): ?string
    {
        return isset($this->lastResult['load_id'])
            ? PortfolioLoadResource::getUrl('view', ['record' => $this->lastResult['load_id']])
            : null;
    }

    protected function formatLatestSuccessLabel(?PortfolioLoad $load): string
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

        return $cache[$column] ??= DbSchema::hasColumn((new PortfolioLoad())->getTable(), $column);
    }
}
