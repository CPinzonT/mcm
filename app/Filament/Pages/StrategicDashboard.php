<?php

namespace App\Filament\Pages;

use App\Data\DashboardFiltersData;
use App\Services\Dashboard\ChartService;
use App\Services\Dashboard\KpiService;
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
    public array $selectedPeriods   = [];
    public array $selectedAdvisors  = [];

    // ── Búsqueda cliente ────────────────────────────────────────
    public string $clientSearch = '';

    // ── Comparación ─────────────────────────────────────────────
    public bool   $compareMode     = false;
    public string $compareType     = 'period';
    public ?string $comparePeriodA = null;
    public ?string $comparePeriodB = null;
    public ?string $compareValueA  = null;
    public ?string $compareValueB  = null;
    public bool   $comparePrevious = false;

    public function mount(): void {}


    // ── Opciones para selects ───────────────────────────────────

    #[Computed]
    public function periodOptions(): array
    {
        return DB::table('portfolio_documents')
            ->selectRaw("DISTINCT LEFT(issue_date, 7) as ym")
            ->whereNotNull('issue_date')
            ->orderByDesc('ym')
            ->pluck('ym')
            ->mapWithKeys(fn ($ym) => [
                $ym => \Carbon\Carbon::parse($ym . '-01')->translatedFormat('F Y'),
            ])
            ->toArray();
    }

    #[Computed]
    public function periodOptionsShort(): array
    {
        return DB::table('portfolio_documents')
            ->selectRaw("DISTINCT LEFT(issue_date, 7) as ym")
            ->whereNotNull('issue_date')
            ->orderByDesc('ym')
            ->pluck('ym')
            ->mapWithKeys(fn ($ym) => [$ym => $ym])
            ->toArray();
    }

    #[Computed]
    public function uenOptions(): array
    {
        return DB::table('clients')
            ->whereNotNull('uen')
            ->where('uen', '!=', '')
            ->distinct()
            ->orderBy('uen')
            ->pluck('uen', 'uen')
            ->toArray();
    }

    #[Computed]
    public function regionalOptions(): array
    {
        return DB::table('clients')
            ->whereNotNull('region')
            ->where('region', '!=', '')
            ->distinct()
            ->orderBy('region')
            ->pluck('region', 'region')
            ->toArray();
    }

    #[Computed]
    public function channelOptions(): array
    {
        return DB::table('clients')
            ->whereNotNull('channel')
            ->where('channel', '!=', '')
            ->distinct()
            ->orderBy('channel')
            ->pluck('channel', 'channel')
            ->toArray();
    }

    #[Computed]
    public function advisorOptions(): array
    {
        return DB::table('advisors')
            ->where('active', true)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }

    #[Computed]
    public function clientOptions(): array
    {
        $q = DB::table('clients')
            ->whereNotNull('name')
            ->where('name', '!=', '')
            ->orderBy('name');

        if ($this->clientSearch) {
            $q->where('name', 'like', '%' . $this->clientSearch . '%');
        }

        return $q->limit(50)->pluck('name', 'id')->toArray();
    }

    // ── Datos reactivos ─────────────────────────────────────────

    #[Computed]
    public function filters(): DashboardFiltersData
    {
        return DashboardFiltersData::fromArray([
            'periods'   => $this->selectedPeriods,
            'channels'  => $this->selectedChannels,
            'uens'      => $this->selectedUens,
            'regionals' => $this->selectedRegionals,
            'advisors'  => $this->selectedAdvisors,
            'client_id' => $this->clientId ?: null,
            'date_from' => $this->dateFrom ?: null,
            'date_to'   => $this->dateTo ?: null,
        ]);
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

    public function applyFilters(): void
    {
        unset($this->kpis, $this->charts, $this->filters, $this->comparison, $this->clientOptions);
        $this->dispatch('charts-updated', charts: $this->charts);
    }

    public function resetFilters(): void
    {
        $this->selectedPeriods   = [];
        $this->selectedChannels  = [];
        $this->selectedUens      = [];
        $this->selectedRegionals = [];
        $this->selectedAdvisors  = [];
        $this->clientId          = null;
        $this->dateFrom          = null;
        $this->dateTo            = null;
        $this->clientSearch      = '';
        $this->applyFilters();
    }

    public function toggleCompare(): void
    {
        $this->compareMode = !$this->compareMode;
        if ($this->compareMode) {
            $this->comparePeriodA = $this->selectedPeriods[0] ?? (array_key_first($this->periodOptions) ?: null);
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
        if (in_array($ch, $this->selectedChannels, true)) {
            $this->selectedChannels = array_values(array_filter($this->selectedChannels, fn ($v) => $v !== $ch));
        } else {
            $this->selectedChannels[] = $ch;
        }
        $this->applyFilters();
    }

    public function toggleUen(string $val): void
    {
        if (in_array($val, $this->selectedUens, true)) {
            $this->selectedUens = array_values(array_filter($this->selectedUens, fn ($v) => $v !== $val));
        } else {
            $this->selectedUens[] = $val;
        }
        $this->applyFilters();
    }

    public function toggleRegional(string $val): void
    {
        if (in_array($val, $this->selectedRegionals, true)) {
            $this->selectedRegionals = array_values(array_filter($this->selectedRegionals, fn ($v) => $v !== $val));
        } else {
            $this->selectedRegionals[] = $val;
        }
        $this->applyFilters();
    }

    public function togglePeriod(string $ym): void
    {
        if (in_array($ym, $this->selectedPeriods, true)) {
            $this->selectedPeriods = array_values(array_filter($this->selectedPeriods, fn ($v) => $v !== $ym));
        } else {
            $this->selectedPeriods[] = $ym;
        }
        $this->applyFilters();
    }

    public function toggleAdvisor(string $id): void
    {
        $id = (int) $id;
        if (in_array($id, $this->selectedAdvisors, true)) {
            $this->selectedAdvisors = array_values(array_filter($this->selectedAdvisors, fn ($v) => $v !== $id));
        } else {
            $this->selectedAdvisors[] = $id;
        }
        $this->applyFilters();
    }

    public function updatedClientSearch(): void
    {
        unset($this->clientOptions);
    }

    // ── Helpers ─────────────────────────────────────────────────

    private function buildComparisonFilters(): array
    {
        $base = [
            'channels'  => $this->selectedChannels,
            'uens'      => $this->selectedUens,
            'regionals' => $this->selectedRegionals,
            'advisors'  => $this->selectedAdvisors,
        ];

        // Para comparación por dimensión usamos el primer período seleccionado (si hay)
        $activePeriod = $this->selectedPeriods[0] ?? null;

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
                DashboardFiltersData::fromArray(array_merge($base, ['uen' => $this->compareValueA, 'period' => $activePeriod])),
                DashboardFiltersData::fromArray(array_merge($base, ['uen' => $this->compareValueB, 'period' => $activePeriod])),
            ];
        }

        if ($this->compareType === 'regional') {
            if (!$this->compareValueA || !$this->compareValueB) return [null, null];
            return [
                DashboardFiltersData::fromArray(array_merge($base, ['regional' => $this->compareValueA, 'period' => $activePeriod])),
                DashboardFiltersData::fromArray(array_merge($base, ['regional' => $this->compareValueB, 'period' => $activePeriod])),
            ];
        }

        return [null, null];
    }

    private function buildPreviousPeriodComparison(): ?array
    {
        $periods = array_keys($this->periodOptions);
        if (count($periods) < 2) return null;

        $currentPeriod = $this->selectedPeriods[0] ?? ($periods[0] ?? null);
        if (!$currentPeriod) return null;

        $idx = array_search($currentPeriod, $periods);
        $previousPeriod = $periods[($idx !== false && $idx + 1 < count($periods)) ? $idx + 1 : 1] ?? null;
        if (!$previousPeriod) return null;

        $base = [
            'channels'  => $this->selectedChannels,
            'uens'      => $this->selectedUens,
            'regionals' => $this->selectedRegionals,
            'advisors'  => $this->selectedAdvisors,
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
