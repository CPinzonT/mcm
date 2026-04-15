<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;

class ManagementDashboardPage extends Page
{
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

    #[Computed]
    public function advisorOptions(): array
    {
        return DB::table('advisors')->where('active', true)->orderBy('name')
            ->pluck('name', 'id')->toArray();
    }

    #[Computed]
    public function periodKpis(): array
    {
        $q = fn (int $days) => DB::table('management_logs')
            ->whereNull('deleted_at')
            ->where('contact_date', '>=', now()->subDays($days)->toDateString());

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
            ->whereNotExists(function ($query) use ($since) {
                $query->selectRaw('1')
                    ->from('management_logs')
                    ->whereColumn('management_logs.client_id', 'clients.id')
                    ->whereNull('management_logs.deleted_at')
                    ->where('management_logs.contact_date', '>=', $since);
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

        $rows = DB::table('management_logs')
            ->whereNull('deleted_at')
            ->where('contact_date', '>=', $since)
            ->select(DB::raw('DATE(contact_date) as day'), DB::raw('COUNT(*) as total'))
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

    public function applyFilter(): void
    {
        unset($this->periodKpis, $this->advisorStats, $this->trendChart);
        $this->dispatch('mgmt-chart-updated', chart: $this->trendChart);
    }
}
