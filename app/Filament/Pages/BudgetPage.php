<?php

namespace App\Filament\Pages;

use App\Data\BudgetFiltersData;
use App\Services\Budget\BudgetAnalyticsService;
use App\Services\Budget\BudgetFilterQueryService;
use App\Services\Budget\BudgetImportService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Livewire\Attributes\Computed;
use Livewire\WithFileUploads;

class BudgetPage extends Page
{
    use WithFileUploads;

    protected string $view = 'filament.pages.budget-page';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Presupuesto';

    protected static ?string $title = 'Presupuesto de recaudo';

    protected static string|\UnitEnum|null $navigationGroup = 'Operación';

    protected static ?int $navigationSort = 4;

    protected static bool $shouldRegisterNavigation = false;

    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable|null
    {
        return null;
    }

    public array $selectedPeriods = [];

    public array $selectedClients = [];

    public array $selectedRegionals = [];

    public array $selectedChannels = [];

    public array $selectedSellers = [];

    public array $selectedTransactionTypes = [];

    public array $selectedCategories = [];

    public string $clientSearch = '';

    public string $sellerSearch = '';

    public ?string $dateFrom = null;

    public ?string $dateTo = null;

    public string $dateField = 'application_date';

    public $uploadFile = null;

    public ?string $uploadPeriodKey = null;

    public ?string $uploadNotes = null;

    public ?array $lastUpload = null;

    #[Computed]
    public function periodOptions(): array
    {
        return app(BudgetFilterQueryService::class)
            ->periodOptions($this->filtersData());
    }

    #[Computed]
    public function periodOptionsShort(): array
    {
        return $this->periodOptions;
    }

    #[Computed]
    public function clientOptions(): array
    {
        return app(BudgetFilterQueryService::class)
            ->clientOptions($this->filtersData(), $this->clientSearch ?: null);
    }

    #[Computed]
    public function regionalOptions(): array
    {
        return app(BudgetFilterQueryService::class)
            ->regionalOptions($this->filtersData());
    }

    #[Computed]
    public function channelOptions(): array
    {
        return app(BudgetFilterQueryService::class)
            ->channelOptions($this->filtersData());
    }

    #[Computed]
    public function sellerOptions(): array
    {
        return app(BudgetFilterQueryService::class)
            ->sellerOptions($this->filtersData(), $this->sellerSearch ?: null);
    }

    #[Computed]
    public function transactionTypeOptions(): array
    {
        return app(BudgetFilterQueryService::class)
            ->transactionTypeOptions($this->filtersData());
    }

    #[Computed]
    public function categoryOptions(): array
    {
        return app(BudgetFilterQueryService::class)
            ->categoryOptions($this->filtersData());
    }

    #[Computed]
    public function summary(): array
    {
        return app(BudgetAnalyticsService::class)->summarize($this->filtersData());
    }

    public function filtersData(): BudgetFiltersData
    {
        return BudgetFiltersData::fromArray([
            'periods'            => $this->selectedPeriods,
            'clients'            => $this->selectedClients,
            'regionals'          => $this->selectedRegionals,
            'channels'           => $this->selectedChannels,
            'sellers'            => $this->selectedSellers,
            'transaction_types'  => $this->selectedTransactionTypes,
            'categories'         => $this->selectedCategories,
            'date_from'          => $this->dateFrom ?: null,
            'date_to'            => $this->dateTo ?: null,
            'date_field'         => $this->dateField,
        ]);
    }

    public function togglePeriod(string $ym): void
    {
        $this->toggleInList($ym, 'selectedPeriods');
    }

    public function toggleClient(string $val): void
    {
        $this->toggleInList(trim($val), 'selectedClients');
    }

    public function toggleRegional(string $val): void
    {
        $this->toggleInList(trim($val), 'selectedRegionals');
    }

    public function toggleChannel(string $val): void
    {
        $this->toggleInList(trim($val), 'selectedChannels');
    }

    public function toggleSeller(string $val): void
    {
        $this->toggleInList(trim($val), 'selectedSellers');
    }

    public function toggleTransactionType(string $val): void
    {
        $this->toggleInList(trim($val), 'selectedTransactionTypes');
    }

    public function toggleCategory(string $val): void
    {
        $this->toggleInList(trim($val), 'selectedCategories');
    }

    public function updatedClientSearch(): void
    {
        unset($this->clientOptions);
    }

    public function updatedSellerSearch(): void
    {
        unset($this->sellerOptions);
    }

    public function updatedDateField(): void
    {
        $this->refreshSummary();
    }

    public function updatedDateFrom(): void
    {
        $this->refreshSummary();
    }

    public function updatedDateTo(): void
    {
        $this->refreshSummary();
    }

    public function resetFilters(): void
    {
        $this->selectedPeriods = [];
        $this->selectedClients = [];
        $this->selectedRegionals = [];
        $this->selectedChannels = [];
        $this->selectedSellers = [];
        $this->selectedTransactionTypes = [];
        $this->selectedCategories = [];
        $this->clientSearch = '';
        $this->sellerSearch = '';
        $this->dateFrom = null;
        $this->dateTo = null;
        $this->dateField = 'application_date';
        $this->refreshSummary();
    }

    public function submitUpload(BudgetImportService $service): void
    {
        $this->validate([
            'uploadFile'      => ['required', 'file', 'mimes:csv,xlsx,xls', 'max:51200'],
            'uploadPeriodKey' => ['nullable', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/'],
            'uploadNotes'     => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $result = $service->handleUpload(
                $this->uploadFile,
                $this->uploadNotes,
                auth()->user(),
                $this->uploadPeriodKey,
            );

            $load = $result['load'];
            $this->lastUpload = [
                'reference'  => $load->reference,
                'valid_rows' => $load->valid_rows,
                'error_rows' => $load->error_rows,
                'total_ppto' => (float) $load->total_amount,
                'total_recaudo' => (float) ($result['total_recaudo'] ?? 0),
                'period_key' => $load->period_key,
                'errors'     => $result['errors'],
            ];

            if ($load->period_key && ! in_array($load->period_key, $this->selectedPeriods, true)) {
                $this->selectedPeriods = [$load->period_key];
            }

            $this->reset('uploadFile', 'uploadNotes');
            $this->refreshSummary();

            $body = "{$load->valid_rows} filas · PPTO $" . number_format((float) $load->total_amount, 0, ',', '.');
            if (($result['total_recaudo'] ?? 0) > 0) {
                $body .= ' · Recaudo $' . number_format((float) $result['total_recaudo'], 0, ',', '.');
            }
            if ($result['errors'] !== []) {
                $body .= ' · ' . count($result['errors']) . ' advertencias.';
            }

            Notification::make()
                ->title('Presupuesto cargado')
                ->body($body)
                ->{$result['errors'] === [] ? 'success' : 'warning'}()
                ->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('No se pudo cargar el presupuesto')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    private function toggleInList(string $val, string $property): void
    {
        if ($val === '') {
            return;
        }
        $list = $this->{$property};
        if (in_array($val, $list, true)) {
            $this->{$property} = array_values(array_filter($list, static fn ($v) => $v !== $val));
        } else {
            $list[] = $val;
            $this->{$property} = $list;
        }
        $this->refreshSummary();
    }

    private function refreshSummary(): void
    {
        unset(
            $this->summary,
            $this->periodOptions,
            $this->clientOptions,
            $this->regionalOptions,
            $this->channelOptions,
            $this->sellerOptions,
            $this->transactionTypeOptions,
            $this->categoryOptions,
        );
    }
}
