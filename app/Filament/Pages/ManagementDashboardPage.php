<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;

class ManagementDashboardPage extends Page
{
    use WithPagination;

    protected string $view = 'filament.pages.management-dashboard';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static string|\UnitEnum|null   $navigationGroup = 'Operación';
    protected static ?string $navigationLabel = 'Gestión de cartera';
    protected static ?string $title  = 'Dashboard de Gestión';
    protected static ?int    $navigationSort = 5;

    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable|null
    {
        return null;
    }

    public int    $trendDays = 30;
    public ?int   $advisorId = null;
    public bool   $dueSoonOnly = false;

    public function mount(): void
    {
        $this->dueSoonOnly = request()->query('follow_up') === '3d';
    }

    #[Computed]
    public function advisorOptions(): array
    {
        return DB::table('advisors')->where('active', true)->orderBy('name')
            ->pluck('name', 'id')->toArray();
    }

    #[Computed]
    public function periodKpis(): array
    {
        $q = fn (int $days) => DB::table('management_logs as ml')
            ->whereNull('ml.deleted_at')
            ->where('ml.contact_date', '>=', now()->subDays($days)->toDateString())
            ->when($this->advisorId, fn ($query) => $query->where('ml.advisor_id', $this->advisorId));

        return [
            'today'   => (clone $q(0))->whereDate('contact_date', today())->count(),
            'week'    => (clone $q(7))->count(),
            'month'   => (clone $q(30))->count(),
            'promises_month'   => (clone $q(30))->where('result', 'promise_to_pay')->count(),
            'fulfilled_month'  => (clone $q(30))->where('result', 'promise_to_pay')->where('status', 'closed')->count(),
            'broken_month'     => (clone $q(30))->where('result', 'promise_to_pay')->where('status', 'open')
                ->whereNotNull('promised_date')->where('promised_date', '<', today()->toDateString())->count(),
            'clients_managed'  => (clone $q(7))->distinct('client_id')->count(),
            'clients_unmanaged'=> $this->clientsWithoutRecentManagement(),
        ];
    }

    private function clientsWithoutRecentManagement(): int
    {
        $since = now()->subDays(7)->toDateString();

        return DB::table('clients')
            ->where('active', true)
            ->whereNull('deleted_at')
            ->when($this->advisorId, function ($query): void {
                $query->whereExists(function ($documents): void {
                    $documents->selectRaw('1')
                        ->from('portfolio_documents as pd')
                        ->whereColumn('pd.client_id', 'clients.id')
                        ->whereNull('pd.deleted_at')
                        ->where('pd.advisor_id', $this->advisorId);
                });
            })
            ->whereNotExists(function ($query) use ($since) {
                $query->selectRaw('1')
                    ->from('management_logs as ml')
                    ->whereColumn('ml.client_id', 'clients.id')
                    ->whereNull('ml.deleted_at')
                    ->where('ml.contact_date', '>=', $since)
                    ->when($this->advisorId, fn ($logs) => $logs->where('ml.advisor_id', $this->advisorId));
            })
            ->count();
    }

    #[Computed]
    public function advisorStats(): array
    {
        $since = now()->subDays(30)->toDateString();

        return DB::table('management_logs as ml')
            ->join('advisors as a', 'a.id', '=', 'ml.advisor_id')
            ->whereNull('ml.deleted_at')
            ->where('ml.contact_date', '>=', $since)
            ->when($this->advisorId, fn ($query) => $query->where('ml.advisor_id', $this->advisorId))
            ->select(
                'a.id', 'a.name',
                DB::raw('COUNT(ml.id) as total_actions'),
                DB::raw('SUM(CASE WHEN ml.result = "promise_to_pay" THEN 1 ELSE 0 END) as promises'),
                DB::raw('SUM(CASE WHEN ml.result = "promise_to_pay" AND ml.status = "closed" THEN 1 ELSE 0 END) as fulfilled'),
                DB::raw('SUM(CASE WHEN ml.result = "promise_to_pay" AND ml.status = "open" AND ml.promised_date < CURDATE() THEN 1 ELSE 0 END) as broken'),
                DB::raw('SUM(COALESCE(ml.promised_amount, 0)) as recovery_value')
            )
            ->groupBy('a.id', 'a.name')
            ->orderByDesc('total_actions')
            ->get()
            ->toArray();
    }

    #[Computed]
    public function trendChart(): array
    {
        $since = now()->subDays($this->trendDays)->toDateString();

        $rows = DB::table('management_logs as ml')
            ->whereNull('ml.deleted_at')
            ->where('ml.contact_date', '>=', $since)
            ->when($this->advisorId, fn ($query) => $query->where('ml.advisor_id', $this->advisorId))
            ->select(DB::raw('DATE(ml.contact_date) as day'), DB::raw('COUNT(*) as total'))
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->keyBy('day');

        $labels = [];
        $data   = [];
        $date   = now()->subDays($this->trendDays);

        while ($date->lte(now())) {
            $day = $date->toDateString();
            $labels[] = $date->format('d/m');
            $data[]   = $rows[$day]->total ?? 0;
            $date->addDay();
        }

        return [
            'labels'   => $labels,
            'datasets' => [[
                'label'           => 'Gestiones',
                'data'            => $data,
                'borderColor'     => '#6366f1',
                'backgroundColor' => 'rgba(99,102,241,0.12)',
                'fill'            => true,
                'tension'         => 0.4,
            ]],
        ];
    }

    #[Computed]
    public function managementRows(): LengthAwarePaginator
    {
        $query = DB::table('management_logs as ml')
            ->join('clients as c', 'c.id', '=', 'ml.client_id')
            ->leftJoin('advisors as a', 'a.id', '=', 'ml.advisor_id')
            ->leftJoin('portfolio_documents as pd', 'pd.id', '=', 'ml.portfolio_document_id')
            ->whereNull('ml.deleted_at')
            ->when($this->advisorId, fn ($builder) => $builder->where('ml.advisor_id', $this->advisorId));

        if ($this->dueSoonOnly) {
            $query
                ->where('ml.status', '!=', 'closed')
                ->whereNotNull('ml.follow_up_date')
                ->whereBetween('ml.follow_up_date', [
                    today()->toDateString(),
                    today()->addDays(3)->toDateString(),
                ])
                ->orderBy('ml.follow_up_date')
                ->orderBy('ml.contact_date');
        } else {
            $query
                ->where('ml.contact_date', '>=', now()->subDays($this->trendDays)->toDateString())
                ->orderByDesc('ml.contact_date')
                ->orderByDesc('ml.contact_time');
        }

        return $query
            ->select([
                'ml.id',
                'ml.contact_date',
                'ml.contact_time',
                'ml.type',
                'ml.subject',
                'ml.result',
                'ml.status',
                'ml.follow_up_date',
                'ml.promised_amount',
                'c.id as client_id',
                'c.name as client',
                'a.name as advisor',
                'pd.document_number',
            ])
            ->paginate(25, pageName: 'managementPage')
            ->through(static fn ($row): array => [
                'id' => (int) $row->id,
                'contact_date' => $row->contact_date,
                'contact_time' => $row->contact_time ? substr((string) $row->contact_time, 0, 5) : null,
                'type' => match ((string) $row->type) {
                    'call' => 'Llamada',
                    'email' => 'Correo',
                    'visit' => 'Visita',
                    'agreement' => 'Acuerdo',
                    'legal' => 'Jurídico',
                    default => 'Otro',
                },
                'subject' => $row->subject,
                'result' => match ((string) $row->result) {
                    'no_contact' => 'Sin contacto',
                    'promise_to_pay' => 'Promesa de pago',
                    'partial_payment' => 'Pago parcial',
                    'refused' => 'Rechazado',
                    'arrangement' => 'Acuerdo',
                    default => 'Otro',
                },
                'status' => match ((string) $row->status) {
                    'closed' => 'Cerrada',
                    'pending' => 'Pendiente',
                    default => 'Abierta',
                },
                'follow_up_date' => $row->follow_up_date,
                'promised_amount' => $row->promised_amount !== null ? (float) $row->promised_amount : null,
                'client_id' => (int) $row->client_id,
                'client' => $row->client,
                'advisor' => $row->advisor,
                'document_number' => $row->document_number,
            ]);
    }

    public function updatedAdvisorId(): void
    {
        $this->applyFilter();
    }

    public function updatedDueSoonOnly(): void
    {
        $this->resetPage(pageName: 'managementPage');
        unset($this->managementRows);
    }

    public function selectAdvisor(int $advisorId): void
    {
        if (! array_key_exists($advisorId, $this->advisorOptions)) {
            return;
        }

        $this->advisorId = $advisorId;
        $this->applyFilter();
    }

    public function applyFilter(): void
    {
        $this->resetPage(pageName: 'managementPage');
        unset($this->periodKpis, $this->advisorStats, $this->trendChart, $this->managementRows);
        $this->dispatch('mgmt-chart-updated', chart: $this->trendChart);
    }
}
