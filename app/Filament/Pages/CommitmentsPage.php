<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;

class CommitmentsPage extends Page
{
    protected string $view = 'filament.pages.commitments-page';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-hand-raised';
    protected static string|\UnitEnum|null   $navigationGroup = 'Operación';
    protected static ?string $navigationLabel = 'Compromisos';
    protected static ?string $title  = 'Compromisos de Pago';
    protected static ?int    $navigationSort = 6;

    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable|null
    {
        return null;
    }

    public ?int $advisorId = null;

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
    public function buckets(): array
    {
        $query = DB::table('management_logs as ml')
            ->join('clients as c', 'c.id', '=', 'ml.client_id')
            ->leftJoin('portfolio_documents as pd', 'pd.id', '=', 'ml.portfolio_document_id')
            ->leftJoin('advisors as a', 'a.id', '=', 'ml.advisor_id')
            ->where('ml.result', 'promise_to_pay')
            ->whereNotNull('ml.promised_date')
            ->whereNull('ml.deleted_at')
            ->select(
                'ml.id', 'ml.promised_date', 'ml.promised_amount',
                'ml.status', 'ml.contact_date',
                'c.id as client_id', 'c.name as client_name', 'c.document_number as nit',
                'pd.document_number', 'a.name as advisor_name'
            )
            ->orderBy('ml.promised_date');

        if ($this->advisorId) {
            $query->where('ml.advisor_id', $this->advisorId);
        }

        $rows = $query->get();
        $today = now()->toDateString();

        $groups = [
            'overdue'   => ['label' => 'Vencidos',    'color' => 'red',   'items' => []],
            'today'     => ['label' => 'Para Hoy',     'color' => 'amber', 'items' => []],
            'upcoming'  => ['label' => 'Próximos',     'color' => 'blue',  'items' => []],
            'fulfilled' => ['label' => 'Cumplidos',    'color' => 'green', 'items' => []],
            'broken'    => ['label' => 'Incumplidos',  'color' => 'gray',  'items' => []],
        ];

        foreach ($rows as $row) {
            $item = [
                'id'              => $row->id,
                'client_name'     => $row->client_name,
                'nit'             => $row->nit,
                'document_number' => $row->document_number,
                'promised_date'   => $row->promised_date,
                'promised_amount' => $row->promised_amount,
                'advisor_name'    => $row->advisor_name ?? '—',
                'status'          => $row->status,
                'contact_date'    => $row->contact_date,
            ];

            if ($row->status === 'closed') {
                $groups['fulfilled']['items'][] = $item;
            } elseif ($row->promised_date < $today && $row->status === 'open') {
                $groups['overdue']['items'][] = $item;
            } elseif ($row->promised_date === $today) {
                $groups['today']['items'][] = $item;
            } elseif ($row->status === 'open') {
                $groups['upcoming']['items'][] = $item;
            } else {
                $groups['broken']['items'][] = $item;
            }
        }

        return $groups;
    }

    #[Computed]
    public function summary(): array
    {
        $b = $this->buckets;
        return [
            'overdue_count'   => count($b['overdue']['items']),
            'today_count'     => count($b['today']['items']),
            'upcoming_count'  => count($b['upcoming']['items']),
            'fulfilled_count' => count($b['fulfilled']['items']),
            'broken_count'    => count($b['broken']['items']),
            'total_pending_value' => collect($b['overdue']['items'])
                ->merge($b['today']['items'])
                ->merge($b['upcoming']['items'])
                ->sum(fn ($i) => (float)($i['promised_amount'] ?? 0)),
        ];
    }

    public function applyFilter(): void
    {
        unset($this->buckets, $this->summary);
    }
}
