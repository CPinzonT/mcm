<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;

class PortfolioManagementDashboard extends Page
{
    protected string $view = 'filament.pages.portfolio-management-dashboard';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-table-cells';
    protected static string|\UnitEnum|null   $navigationGroup = 'Operación';
    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $navigationLabel = 'Gestión de Cartera';
    protected static ?string $title  = 'Dashboard de Gestión de Cartera';
    protected static ?int    $navigationSort = 2;

    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable|null
    {
        return null;
    }

    public ?string $period   = null;
    public ?string $uen      = null;
    public ?string $regional = null;
    public ?string $channel  = null;
    public string  $view_tab = 'executive'; // 'executive' | 'operational'

    public function mount(): void {}

    // ── Opciones de filtro ────────────────────────────────────────────────────

    #[Computed]
    public function periodOptions(): array
    {
        return DB::table('portfolio_documents')
            ->selectRaw('LEFT(period_date, 7) as pk')
            ->whereNotNull('period_date')
            ->groupBy('pk')
            ->orderByDesc('pk')
            ->pluck('pk', 'pk')
            ->toArray();
    }

    #[Computed]
    public function uenOptions(): array
    {
        return DB::table('clients')
            ->whereNotNull('uen')->where('uen', '!=', '')
            ->distinct()->orderBy('uen')
            ->pluck('uen', 'uen')->toArray();
    }

    #[Computed]
    public function regionalOptions(): array
    {
        return DB::table('clients')
            ->whereNotNull('region')->where('region', '!=', '')
            ->distinct()->orderBy('region')
            ->pluck('region', 'region')->toArray();
    }

    // ── Base query ────────────────────────────────────────────────────────────

    private function portfolioBase()
    {
        $q = DB::table('portfolio_documents as pd')
            ->join('portfolio_loads as pl', 'pl.id', '=', 'pd.portfolio_load_id')
            ->join('clients as c', 'c.id', '=', 'pd.client_id')
            ->where('pl.is_active', true)
            ->where('pl.status', 'completed')
            ->whereIn('pd.status', ['active', 'partial', 'in_process'])
            ->whereNull('pd.deleted_at');

        $this->applyPortfolioFilters($q);
        return $q;
    }

    private function applyPortfolioFilters($q): void
    {
        if ($this->period) {
            $q->where('pd.period_date', $this->period);
        } else {
            $latest = DB::table('portfolio_documents')->max('period_date');
            if ($latest) $q->where('pd.period_date', $latest);
        }
        if ($this->uen)      $q->where('c.uen', $this->uen);
        if ($this->regional) $q->where('c.region', $this->regional);
        if ($this->channel)  $q->where('c.channel', $this->channel);
    }

    // ── KPIs ──────────────────────────────────────────────────────────────────

    #[Computed]
    public function kpis(): array
    {
        $base = $this->portfolioBase();

        $row = (clone $base)->selectRaw('
            SUM(pd.pending_amount) as total,
            SUM(CASE WHEN pd.days_overdue > 0 THEN pd.pending_amount ELSE 0 END) as overdue,
            SUM(CASE WHEN pd.days_overdue > 90 THEN pd.pending_amount ELSE 0 END) as critical,
            COUNT(DISTINCT pd.client_id) as total_clients,
            COUNT(DISTINCT CASE WHEN pd.days_overdue > 0 THEN pd.client_id END) as clients_overdue,
            COUNT(pd.id) as total_docs
        ')->first();

        $total    = (float) ($row->total    ?? 0);
        $overdue  = (float) ($row->overdue  ?? 0);
        $critical = (float) ($row->critical ?? 0);

        // Recaudo del período
        $periodStart = $this->period
            ? \Carbon\Carbon::parse($this->period)->startOfMonth()->toDateString()
            : \Carbon\Carbon::parse(DB::table('portfolio_documents')->max('period_date') ?? now())->startOfMonth()->toDateString();
        $periodEnd   = \Carbon\Carbon::parse($periodStart)->endOfMonth()->toDateString();

        $collected = (float) DB::table('collection_details')
            ->join('collection_loads as cl', 'cl.id', '=', 'collection_details.collection_load_id')
            ->where('cl.is_active', true)->where('cl.status', 'completed')
            ->whereBetween('payment_date', [$periodStart, $periodEnd])
            ->sum('amount');

        $dso = ($total > 0 && $collected > 0) ? round($total / $collected * 30, 1) : null;

        // Promesas
        $promises = DB::table('management_logs')
            ->select(
                DB::raw('SUM(CASE WHEN status = "open" AND promised_date >= CURDATE() THEN 1 ELSE 0 END) as pending'),
                DB::raw('SUM(CASE WHEN status = "closed" AND result = "promise_to_pay" THEN 1 ELSE 0 END) as fulfilled'),
                DB::raw('SUM(CASE WHEN status = "open" AND promised_date < CURDATE() THEN 1 ELSE 0 END) as broken'),
                DB::raw('SUM(CASE WHEN status = "open" THEN COALESCE(promised_amount, 0) ELSE 0 END) as pending_value')
            )
            ->where('result', 'promise_to_pay')
            ->whereNull('deleted_at')
            ->first();

        return [
            'portfolio_total'       => $total,
            'overdue_total'         => $overdue,
            'overdue_rate'          => $total > 0 ? round($overdue / $total * 100, 1) : 0,
            'critical_amount'       => $critical,
            'critical_rate'         => $total > 0 ? round($critical / $total * 100, 1) : 0,
            'dso'                   => $dso,
            'collected_this_period' => $collected,
            'total_clients'         => (int) ($row->total_clients ?? 0),
            'clients_overdue'       => (int) ($row->clients_overdue ?? 0),
            'total_docs'            => (int) ($row->total_docs ?? 0),
            'promises_pending'      => (int) ($promises->pending  ?? 0),
            'promises_fulfilled'    => (int) ($promises->fulfilled ?? 0),
            'promises_broken'       => (int) ($promises->broken    ?? 0),
            'promises_value'        => (float) ($promises->pending_value ?? 0),
            'portfolio_empty'       => $total == 0,
        ];
    }

    // ── Datos operativos ──────────────────────────────────────────────────────

    #[Computed]
    public function byClient(): array
    {
        return $this->portfolioBase()
            ->select(
                'c.id', 'c.name', 'c.document_number as nit',
                DB::raw('COUNT(pd.id) as total_docs'),
                DB::raw('SUM(pd.pending_amount) as total_balance'),
                DB::raw('SUM(CASE WHEN pd.days_overdue > 0 THEN pd.pending_amount ELSE 0 END) as overdue_balance'),
                DB::raw('MAX(pd.days_overdue) as days_overdue_max')
            )
            ->groupBy('c.id', 'c.name', 'c.document_number')
            ->orderByDesc('total_balance')
            ->limit(50)
            ->get()
            ->toArray();
    }

    #[Computed]
    public function byDocument(): array
    {
        return $this->portfolioBase()
            ->where('pd.days_overdue', '>', 0)
            ->select(
                'pd.document_number', 'c.name as client_name',
                'pd.days_overdue', 'pd.pending_amount', 'pd.due_date', 'pd.risk_level'
            )
            ->orderByDesc('pd.days_overdue')
            ->limit(100)
            ->get()
            ->toArray();
    }

    #[Computed]
    public function recentLogs(): array
    {
        return DB::table('management_logs as ml')
            ->join('clients as c', 'c.id', '=', 'ml.client_id')
            ->leftJoin('portfolio_documents as pd', 'pd.id', '=', 'ml.portfolio_document_id')
            ->leftJoin('advisors as a', 'a.id', '=', 'ml.advisor_id')
            ->whereNull('ml.deleted_at')
            ->select(
                'ml.contact_date', 'c.name as client_name',
                'pd.document_number',
                'ml.type', 'ml.result', 'a.name as advisor_name'
            )
            ->orderByDesc('ml.contact_date')
            ->limit(20)
            ->get()
            ->map(fn ($r) => [
                'contact_date'  => $r->contact_date,
                'client_name'   => $r->client_name,
                'document_number' => $r->document_number,
                'type_label'    => match($r->type) {
                    'call'      => 'Llamada',
                    'email'     => 'Correo',
                    'visit'     => 'Visita',
                    'agreement' => 'Acuerdo',
                    'legal'     => 'Jurídico',
                    default     => 'Otro',
                },
                'result_label'  => match($r->result) {
                    'promise_to_pay'  => 'Promesa pago',
                    'partial_payment' => 'Pago parcial',
                    'no_contact'      => 'Sin contacto',
                    'arrangement'     => 'Acuerdo',
                    'refused'         => 'Rechazó',
                    default           => 'Otro',
                },
                'advisor_name'  => $r->advisor_name ?? '—',
            ])
            ->toArray();
    }

    // ── Datos de gráficos ─────────────────────────────────────────────────────

    #[Computed]
    public function charts(): array
    {
        $base = $this->portfolioBase();

        // Aging doughnut
        $aging = (clone $base)->selectRaw('
            SUM(CASE WHEN pd.days_overdue <= 0 THEN pd.pending_amount ELSE 0 END) as corriente,
            SUM(CASE WHEN pd.days_overdue BETWEEN 1 AND 30 THEN pd.pending_amount ELSE 0 END) as b1_30,
            SUM(CASE WHEN pd.days_overdue BETWEEN 31 AND 60 THEN pd.pending_amount ELSE 0 END) as b31_60,
            SUM(CASE WHEN pd.days_overdue BETWEEN 61 AND 90 THEN pd.pending_amount ELSE 0 END) as b61_90,
            SUM(CASE WHEN pd.days_overdue > 90 THEN pd.pending_amount ELSE 0 END) as b90p
        ')->first();

        $agingValues = $aging ? [
            (float)$aging->corriente,
            (float)$aging->b1_30,
            (float)$aging->b31_60,
            (float)$aging->b61_90,
            (float)$aging->b90p,
        ] : [0,0,0,0,0];

        $agingChart = [
            'labels'   => ['Corriente', '1-30 días', '31-60 días', '61-90 días', '+90 días'],
            'datasets' => [[
                'data' => $agingValues,
                'backgroundColor' => ['#10b981','#fbbf24','#f97316','#ef4444','#991b1b'],
            ]],
        ];

        // Top 10 clients pareto
        $topClients = (clone $base)
            ->select('c.name as client_name', DB::raw('SUM(pd.pending_amount) as total'))
            ->groupBy('c.id', 'c.name')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $total = $topClients->sum('total') ?: 1;
        $cum = 0;
        $cumPcts = [];
        foreach ($topClients as $row) {
            $cum += $row->total;
            $cumPcts[] = round($cum / $total * 100, 1);
        }

        $paretoChart = [
            'labels' => $topClients->map(fn ($r) => Str::limit($r->client_name, 15))->toArray(),
            'datasets' => [
                [
                    'type' => 'bar', 'label' => 'Saldo',
                    'data' => $topClients->map(fn ($r) => (float)$r->total)->toArray(),
                    'backgroundColor' => '#3b82f6', 'yAxisID' => 'y',
                ],
                [
                    'type' => 'line', 'label' => '% Acumulado',
                    'data' => $cumPcts,
                    'borderColor' => '#f59e0b', 'backgroundColor' => 'transparent',
                    'yAxisID' => 'y1', 'tension' => 0.4,
                ],
            ],
        ];

        return ['aging' => $agingChart, 'pareto' => $paretoChart];
    }

    // ── Acciones ──────────────────────────────────────────────────────────────

    public function runFilter(): void
    {
        unset($this->kpis, $this->byClient, $this->byDocument, $this->recentLogs, $this->charts);
        $this->dispatch('pmcharts-updated', charts: $this->charts);
    }

    public function resetFilters(): void
    {
        $this->period = $this->uen = $this->regional = $this->channel = null;
        $this->runFilter();
    }
}
