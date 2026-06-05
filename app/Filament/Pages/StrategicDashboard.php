<?php

namespace App\Filament\Pages;

use App\Data\DashboardFiltersData;
use App\Filament\Resources\AdvisorResource;
use App\Filament\Resources\ClientResource;
use App\Filament\Resources\PortfolioDocumentResource;
use App\Services\Dashboard\ChartService;
use App\Services\Dashboard\DashboardFilterCascadeService;
use App\Services\Dashboard\KpiService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;

class StrategicDashboard extends Page
{
    protected string $view = 'filament.pages.strategic-dashboard';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-home';
    protected static ?string $navigationLabel = 'Dashboard';
    protected static ?string $title = 'Dashboard Estratégico — Cartera';
    protected static ?int $navigationSort = -1;

    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable|null
    {
        return null;
    }

    // ── Filtros simples ──────────────────────────────────────────
    public ?string $clientId  = null;
    public ?string $dateFrom  = null;
    public ?string $dateTo    = null;

    // ── Filtros multi-select (Power BI style) ────────────────────
    public array $selectedChannels  = [];
    public array $selectedUens      = [];
    public array $selectedRegionals = [];
    public array $selectedAdvisors  = [];

    /** @var string[] valores portfolio_documents.document_type */
    public array $selectedDocumentTypes = [];

    /** @var string[] claves risk_level (normal, low, …) — filtro cruzado desde gráfica Aging */
    public array $selectedRiskLevels = [];

    // ── Búsqueda cliente / asesor ───────────────────────────────
    public string $clientSearch = '';

    public string $advisorSearch = '';

    // ── Comparación ─────────────────────────────────────────────
    public bool   $compareMode     = false;
    public string $compareType     = 'period';
    public ?string $comparePeriodA = null;
    public ?string $comparePeriodB = null;
    public ?string $compareValueA  = null;
    public ?string $compareValueB  = null;
    public bool   $comparePrevious = false;

    public function mount(): void {}

    /**
     * Livewire puede hidratar IDs como string; normalizamos para que la cascada y in_array(..., true) sean coherentes.
     */
    public function hydrate(): void
    {
        $this->normalizeAdvisorIds();
    }

    // ── Opciones para selects ───────────────────────────────────

    #[Computed]
    public function accountingMonthOptions(): array
    {
        return collect(app(DashboardFilterCascadeService::class)->loadedAccountingMonthOptions())
            ->mapWithKeys(fn ($ym) => [
                $ym => \Carbon\Carbon::parse($ym . '-01')->translatedFormat('F Y'),
            ])
            ->toArray();
    }

    #[Computed]
    public function accountingMonthOptionsShort(): array
    {
        return app(DashboardFilterCascadeService::class)->accountingMonthOptions($this->dashboardFiltersData());
    }

    #[Computed]
    public function uenOptions(): array
    {
        return app(DashboardFilterCascadeService::class)
            ->uenOptions($this->dashboardFiltersData());
    }

    #[Computed]
    public function regionalOptions(): array
    {
        return app(DashboardFilterCascadeService::class)
            ->regionalOptions($this->dashboardFiltersData());
    }

    #[Computed]
    public function channelOptions(): array
    {
        return app(DashboardFilterCascadeService::class)
            ->channelOptions($this->dashboardFiltersData());
    }

    #[Computed]
    public function advisorOptions(): array
    {
        return app(DashboardFilterCascadeService::class)
            ->advisorOptions($this->dashboardFiltersData(), $this->advisorSearch, 80);
    }

    #[Computed]
    public function documentTypeOptions(): array
    {
        return app(DashboardFilterCascadeService::class)
            ->documentTypeOptions($this->dashboardFiltersData());
    }

    #[Computed]
    public function clientOptions(): array
    {
        $limit = !empty($this->selectedAdvisors) ? 500 : 50;

        return app(DashboardFilterCascadeService::class)
            ->clientOptions($this->dashboardFiltersData(), $this->clientSearch, $limit);
    }

    // ── Datos reactivos ─────────────────────────────────────────

    /** Misma data que {@see filters()} sin depender del cache Computed (para cascada y poda). */
    private function dashboardFiltersData(): DashboardFiltersData
    {
        return DashboardFiltersData::fromArray([
            'channels'       => $this->selectedChannels,
            'uens'           => $this->selectedUens,
            'regionals'      => $this->selectedRegionals,
            'advisors'       => $this->selectedAdvisors,
            'risk_levels'    => $this->selectedRiskLevels,
            'document_types' => $this->selectedDocumentTypes,
            'client_id'      => $this->clientId ?: null,
            'date_from'      => $this->dateFrom ?: null,
            'date_to'        => $this->dateTo ?: null,
        ]);
    }

    #[Computed]
    public function filters(): DashboardFiltersData
    {
        return $this->dashboardFiltersData();
    }

    #[Computed]
    public function activeDateRangeLabel(): string
    {
        return $this->filters->periodLabel();
    }

    #[Computed]
    public function moraConsultationLabel(): string
    {
        return \Carbon\Carbon::parse($this->filters->consultationDate())->format('d/m/Y');
    }

    #[Computed]
    public function kpis(): array
    {
        return app(KpiService::class)->compute($this->filters);
    }

    #[Computed]
    public function charts(): array
    {
        return app(ChartService::class)->compute($this->filters);
    }

    #[Computed]
    public function comparison(): ?array
    {
        if (!$this->compareMode) return null;

        if ($this->comparePrevious) {
            return $this->buildPreviousPeriodComparison();
        }

        [$filtersA, $filtersB] = $this->buildComparisonFilters();
        if (!$filtersA || !$filtersB) return null;

        $kpiService = app(KpiService::class);
        $kpisA = $kpiService->compute($filtersA);
        $kpisB = $kpiService->compute($filtersB);

        return [
            'a' => ['label' => $this->labelA(), 'kpis' => $kpisA],
            'b' => ['label' => $this->labelB(), 'kpis' => $kpisB],
            'deltas' => $this->computeDeltas($kpisA, $kpisB),
        ];
    }

    // ── Acciones ────────────────────────────────────────────────

    #[Computed]
    public function hasActiveFilters(): bool
    {
        return $this->selectedChannels !== []
            || $this->selectedUens !== []
            || $this->selectedRegionals !== []
            || $this->selectedAdvisors !== []
            || $this->selectedRiskLevels !== []
            || $this->selectedDocumentTypes !== []
            || ($this->clientId !== null && $this->clientId !== '')
            || ($this->dateFrom !== null && $this->dateFrom !== '')
            || ($this->dateTo !== null && $this->dateTo !== '');
    }

    public function applyFilters(): void
    {
        $this->normalizeAdvisorIds();
        $this->pruneInvalidSelections();

        unset(
            $this->kpis,
            $this->charts,
            $this->filters,
            $this->comparison,
            $this->clientOptions,
            $this->uenOptions,
            $this->channelOptions,
            $this->regionalOptions,
            $this->advisorOptions,
            $this->documentTypeOptions,
            $this->accountingMonthOptions,
            $this->accountingMonthOptionsShort,
            $this->activeDateRangeLabel,
        );
        $this->dispatch('charts-updated', charts: $this->charts);
    }

    public function resetFilters(): void
    {
        $this->selectedChannels  = [];
        $this->selectedUens      = [];
        $this->selectedRegionals = [];
        $this->selectedAdvisors  = [];
        $this->selectedRiskLevels = [];
        $this->selectedDocumentTypes = [];
        $this->clientId          = null;
        $this->dateFrom          = null;
        $this->dateTo            = null;
        $this->clientSearch      = '';
        $this->advisorSearch     = '';
        $this->applyFilters();
    }

    /**
     * Filtro tipo Power BI: clic en una gráfica acota el tablero al segmento y ofrece atajo al detalle.
     *
     * @param  string|null  $payload  UEN, canal, período YYYY-MM, clave risk_level o ID numérico
     */
    public function filterFromChart(string $chart, ?string $payload = null): void
    {
        $allowedRisk = ['normal', 'low', 'medium', 'high', 'critical'];
        $detailUrl   = null;
        $summary     = '';

        switch ($chart) {
            case 'aging':
                $bucketToRisk = [
                    'actual' => 'normal',
                    '1_30_dias' => 'low',
                    '31_60_dias' => 'medium',
                    '61_90_dias' => 'high',
                    '91_180_dias' => 'critical',
                    '181_360_dias' => 'critical',
                    '361_dias' => 'critical',
                ];
                $bucketLabels = [
                    'actual' => 'Corriente',
                    '1_30_dias' => '1-30 días',
                    '31_60_dias' => '31-60 días',
                    '61_90_dias' => '61-90 días',
                    '91_180_dias' => '91-180 días',
                    '181_360_dias' => '181-360 días',
                    '361_dias' => '+360 días',
                ];
                if ($payload && isset($bucketToRisk[$payload])) {
                    $this->selectedRiskLevels = [$bucketToRisk[$payload]];
                    $summary = 'Antigüedad: ' . ($bucketLabels[$payload] ?? $payload);
                } elseif ($payload && in_array($payload, $allowedRisk, true)) {
                    $this->selectedRiskLevels = [$payload];
                    $riskLabels = [
                        'normal' => 'Corriente', 'low' => '1-30 días', 'medium' => '31-60 días',
                        'high' => '61-90 días', 'critical' => '91+ días',
                    ];
                    $summary = 'Antigüedad: ' . ($riskLabels[$payload] ?? $payload);
                } else {
                    return;
                }
                $detailUrl = PortfolioDocumentResource::getUrl('index');
                break;

            case 'trend':
                if (!$payload) {
                    return;
                }
                $start = \Carbon\Carbon::parse($payload . '-01')->startOfMonth();
                $end   = $start->copy()->endOfMonth();
                $this->dateFrom = $start->toDateString();
                $this->dateTo   = $end->toDateString();
                $summary        = 'Contabilización: ' . $start->translatedFormat('F Y');
                $detailUrl      = PortfolioDocumentResource::getUrl('index');
                break;

            case 'by_uen':
                if ($payload === null || $payload === '') {
                    return;
                }
                $this->selectedUens = [$payload];
                $summary            = 'UEN: ' . $payload;
                $detailUrl          = ClientResource::getUrl('index');
                break;

            case 'by_channel':
                if ($payload === null || $payload === '') {
                    return;
                }
                $this->selectedChannels = [$payload];
                $summary                = 'Canal: ' . $payload;
                $detailUrl              = ClientResource::getUrl('index');
                break;

            case 'by_advisor':
                $id = (int) ($payload ?? 0);
                if ($id < 1) {
                    return;
                }
                $this->selectedAdvisors = [$id];
                $summary                = 'Asesor seleccionado';
                $detailUrl              = AdvisorResource::getUrl('edit', ['record' => $id]);
                break;

            case 'pareto':
                $id = (int) ($payload ?? 0);
                if ($id < 1) {
                    return;
                }
                $this->clientId = (string) $id;
                $name           = DB::table('clients')->where('id', $id)->value('name');
                $summary        = 'Cliente: ' . ($name ?: '#' . $id);
                $detailUrl      = ClientResource::getUrl('view', ['record' => $id]);
                break;

            default:
                return;
        }

        $this->applyFilters();

        $notification = Notification::make()
            ->title('Segmento aplicado')
            ->body('Los KPI y gráficos reflejan: ' . $summary . '.')
            ->success();

        if ($detailUrl !== null) {
            $notification->actions([
                Action::make('drill')
                    ->label('Ver detalle')
                    ->url($detailUrl)
                    ->link(),
            ]);
        }

        $notification->send();
    }

    public function toggleDocumentType(string $type): void
    {
        if (in_array($type, $this->selectedDocumentTypes, true)) {
            $this->selectedDocumentTypes = array_values(array_filter(
                $this->selectedDocumentTypes,
                fn ($v) => $v !== $type
            ));
        } else {
            $this->selectedDocumentTypes[] = $type;
        }
        $this->applyFilters();
    }

    public function toggleCompare(): void
    {
        $this->compareMode = !$this->compareMode;
        if ($this->compareMode) {
            $this->comparePeriodA = array_key_first($this->accountingMonthOptions) ?: null;
        }
        unset($this->comparison);
    }

    public function toggleComparePrevious(): void
    {
        $this->comparePrevious = !$this->comparePrevious;
        if ($this->comparePrevious) {
            $this->compareMode = true;
        }
        unset($this->comparison);
    }

    public function setClient(string $id): void
    {
        $this->clientId = $id ?: null;
        $this->applyFilters();
    }

    public function toggleChannel(string $ch): void
    {
        $ch = trim($ch);
        if ($ch === '') {
            return;
        }
        if (in_array($ch, array_map('trim', $this->selectedChannels), true)) {
            $this->selectedChannels = array_values(array_filter(
                $this->selectedChannels,
                static fn ($v) => trim((string) $v) !== $ch
            ));
        } else {
            $this->selectedChannels[] = $ch;
        }
        $this->applyFilters();
    }

    public function toggleUen(string $val): void
    {
        $val = trim($val);
        if ($val === '') {
            return;
        }
        if (in_array($val, array_map('trim', $this->selectedUens), true)) {
            $this->selectedUens = array_values(array_filter(
                $this->selectedUens,
                static fn ($v) => trim((string) $v) !== $val
            ));
        } else {
            $this->selectedUens[] = $val;
        }
        $this->applyFilters();
    }

    public function toggleRegional(string $val): void
    {
        $val = trim($val);
        if ($val === '') {
            return;
        }
        if (in_array($val, array_map('trim', $this->selectedRegionals), true)) {
            $this->selectedRegionals = array_values(array_filter(
                $this->selectedRegionals,
                static fn ($v) => trim((string) $v) !== $val
            ));
        } else {
            $this->selectedRegionals[] = $val;
        }
        $this->applyFilters();
    }

    public function updatedDateFrom(): void
    {
        $this->applyFilters();
    }

    public function updatedDateTo(): void
    {
        $this->applyFilters();
    }

    /** Uso desde gráficos / acciones programáticas. */
    public function toggleAdvisor(string $id): void
    {
        $this->normalizeAdvisorIds();
        $id = (int) $id;
        if ($id < 1) {
            return;
        }
        if (in_array($id, $this->selectedAdvisors, true)) {
            $this->selectedAdvisors = array_values(array_filter(
                $this->selectedAdvisors,
                static fn ($v) => (int) $v !== $id
            ));
        } else {
            $this->selectedAdvisors[] = $id;
        }
        $this->normalizeAdvisorIds();
        $this->applyFilters();
    }

    public function updatedClientSearch(): void
    {
        unset($this->clientOptions);
    }

    public function updatedAdvisorSearch(): void
    {
        unset($this->advisorOptions);
    }

    /**
     * Elimina selecciones que ya no tienen documentos de cartera bajo el resto de filtros (cascada).
     */
    private function pruneInvalidSelections(): void
    {
        $cascade = app(DashboardFilterCascadeService::class);

        for ($pass = 0; $pass < 2; $pass++) {
            $f = $this->dashboardFiltersData();
            if ($this->clientId !== null && $this->clientId !== '') {
                $exists = $cascade->baseQuery($f, ['client'])
                    ->where('pd.client_id', (int) $this->clientId)
                    ->exists();
                if (!$exists) {
                    $this->clientId = null;
                }
            }

            $f = $this->dashboardFiltersData();
            $baseArr = [
                'channels'       => $this->selectedChannels,
                'uens'           => $this->selectedUens,
                'regionals'      => $this->selectedRegionals,
                'advisors'       => [],
                'risk_levels'    => $this->selectedRiskLevels,
                'document_types' => $this->selectedDocumentTypes,
                'client_id'      => $this->clientId ?: null,
                'date_from'      => $this->dateFrom ?: null,
                'date_to'        => $this->dateTo ?: null,
            ];
            $fNoAdv = DashboardFiltersData::fromArray($baseArr);
            $keepAdvisors = [];
            foreach (array_map('intval', $this->selectedAdvisors) as $aid) {
                if ($aid < 1) {
                    continue;
                }
                $fAdvisor = DashboardFiltersData::fromArray(array_merge($baseArr, ['advisors' => [$aid]]));
                // Sin corte contable (Desde/Hasta): el asesor no se desmarca si aún tiene cartera en el corte activo.
                if ($cascade->baseQuery($fAdvisor, ['accounting'])->exists()) {
                    $keepAdvisors[] = $aid;
                }
            }
            $this->selectedAdvisors = array_values($keepAdvisors);

            $f = $this->dashboardFiltersData();
            $this->selectedChannels = $cascade->pruneDimensionsToAllowedKeys(
                $this->selectedChannels,
                $cascade->channelOptions($f)
            );

            $f = $this->dashboardFiltersData();
            $this->selectedRegionals = $cascade->pruneDimensionsToAllowedKeys(
                $this->selectedRegionals,
                $cascade->regionalOptions($f)
            );

            $f = $this->dashboardFiltersData();
            $this->selectedDocumentTypes = array_values(array_intersect(
                $this->selectedDocumentTypes,
                array_keys($cascade->documentTypeOptions($f))
            ));

            $f = $this->dashboardFiltersData();
            $allowedRisk = $cascade->baseQuery($f, ['risk_levels'])
                ->whereNotNull('pd.risk_level')
                ->where('pd.risk_level', '!=', '')
                ->distinct()
                ->pluck('pd.risk_level')
                ->all();
            $this->selectedRiskLevels = array_values(array_intersect($this->selectedRiskLevels, $allowedRisk));

            $f = $this->dashboardFiltersData();
            $this->selectedUens = $cascade->pruneDimensionsToAllowedKeys(
                $this->selectedUens,
                $cascade->uenOptions($f)
            );
        }
    }

    // ── Helpers ─────────────────────────────────────────────────

    private function normalizeAdvisorIds(): void
    {
        $this->selectedAdvisors = array_values(array_unique(array_map(
            static fn ($v) => (int) $v,
            array_filter((array) $this->selectedAdvisors, static fn ($v) => is_numeric($v) && (int) $v > 0)
        )));
    }

    private function buildComparisonFilters(): array
    {
        $base = [
            'channels'       => $this->selectedChannels,
            'uens'           => $this->selectedUens,
            'regionals'      => $this->selectedRegionals,
            'advisors'       => $this->selectedAdvisors,
            'document_types' => $this->selectedDocumentTypes,
            'risk_levels'    => $this->selectedRiskLevels,
            'date_from'      => $this->dateFrom ?: null,
            'date_to'        => $this->dateTo ?: null,
        ];

        if ($this->compareType === 'period') {
            if (!$this->comparePeriodA || !$this->comparePeriodB) return [null, null];
            return [
                DashboardFiltersData::fromArray(array_merge($base, ['period' => $this->comparePeriodA])),
                DashboardFiltersData::fromArray(array_merge($base, ['period' => $this->comparePeriodB])),
            ];
        }

        if ($this->compareType === 'uen') {
            if (!$this->compareValueA || !$this->compareValueB) return [null, null];
            return [
                DashboardFiltersData::fromArray(array_merge($base, ['uen' => $this->compareValueA])),
                DashboardFiltersData::fromArray(array_merge($base, ['uen' => $this->compareValueB])),
            ];
        }

        if ($this->compareType === 'regional') {
            if (!$this->compareValueA || !$this->compareValueB) return [null, null];
            return [
                DashboardFiltersData::fromArray(array_merge($base, ['regional' => $this->compareValueA])),
                DashboardFiltersData::fromArray(array_merge($base, ['regional' => $this->compareValueB])),
            ];
        }

        return [null, null];
    }

    private function buildPreviousPeriodComparison(): ?array
    {
        $periods = array_keys($this->accountingMonthOptions);
        if (count($periods) < 2) return null;

        $currentPeriod = $this->dateFrom
            ? \Carbon\Carbon::parse($this->dateFrom)->format('Y-m')
            : ($periods[0] ?? null);
        if (!$currentPeriod) return null;

        $idx = array_search($currentPeriod, $periods, true);
        $previousPeriod = $periods[($idx !== false && $idx + 1 < count($periods)) ? $idx + 1 : 1] ?? null;
        if (!$previousPeriod) return null;

        $base = [
            'channels'       => $this->selectedChannels,
            'uens'           => $this->selectedUens,
            'regionals'      => $this->selectedRegionals,
            'advisors'       => $this->selectedAdvisors,
            'document_types' => $this->selectedDocumentTypes,
            'risk_levels'    => $this->selectedRiskLevels,
        ];

        $kpiService = app(KpiService::class);
        $filtersA = DashboardFiltersData::fromArray(array_merge($base, ['period' => $currentPeriod]));
        $filtersB = DashboardFiltersData::fromArray(array_merge($base, ['period' => $previousPeriod]));
        $kpisA = $kpiService->compute($filtersA);
        $kpisB = $kpiService->compute($filtersB);

        return [
            'a' => ['label' => \Carbon\Carbon::parse($currentPeriod . '-01')->translatedFormat('M Y'), 'kpis' => $kpisA],
            'b' => ['label' => \Carbon\Carbon::parse($previousPeriod . '-01')->translatedFormat('M Y'), 'kpis' => $kpisB],
            'deltas' => $this->computeDeltas($kpisA, $kpisB),
        ];
    }

    private function computeDeltas(array $a, array $b): array
    {
        $delta = function ($key) use ($a, $b) {
            $va = $a[$key] ?? 0;
            $vb = $b[$key] ?? 0;
            if (!$vb || !$va) return ['value' => 0, 'direction' => 'flat'];
            $change = round((($va - $vb) / abs($vb)) * 100, 1);
            return [
                'value' => abs($change),
                'direction' => $change > 0 ? 'up' : ($change < 0 ? 'down' : 'flat'),
            ];
        };

        return [
            'portfolio_total' => $delta('portfolio_total'),
            'critical_rate'   => $delta('critical_rate'),
            'ism'             => $delta('ism'),
            'recaudo_period'  => $delta('recaudo_period'),
            'recovery_rate'   => $delta('recovery_rate'),
            'score'           => $delta('score'),
            'rotation'        => $delta('rotation'),
        ];
    }

    private function labelA(): string
    {
        return match($this->compareType) {
            'period'   => \Carbon\Carbon::parse($this->comparePeriodA . '-01')->translatedFormat('M Y'),
            'uen'      => 'UEN: ' . $this->compareValueA,
            'regional' => 'Regional: ' . $this->compareValueA,
            default    => 'A',
        };
    }

    private function labelB(): string
    {
        return match($this->compareType) {
            'period'   => \Carbon\Carbon::parse($this->comparePeriodB . '-01')->translatedFormat('M Y'),
            'uen'      => 'UEN: ' . $this->compareValueB,
            'regional' => 'Regional: ' . $this->compareValueB,
            default    => 'B',
        };
    }
}
