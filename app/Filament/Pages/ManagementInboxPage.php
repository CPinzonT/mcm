<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;

class ManagementInboxPage extends Page
{
    protected string $view = 'filament.pages.management-inbox';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-inbox-stack';
    protected static string|\UnitEnum|null   $navigationGroup = 'Operación';
    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $navigationLabel = 'Bandeja de Gestión';
    protected static ?string $title  = 'Bandeja de Gestión';
    protected static ?int    $navigationSort = 5;

    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable|null
    {
        return null;
    }

    public ?int    $advisorId = null;
    public ?string $minBucket = null;   // e.g. '31-60'
    public ?string $search    = null;

    #[Computed]
    public function advisorOptions(): array
    {
        return DB::table('advisors')->where('active', true)->orderBy('name')
            ->pluck('name', 'id')->toArray();
    }

    #[Computed]
    public function items(): array
    {
        $q = DB::table('portfolio_documents as pd')
            ->join('portfolio_loads as pl', 'pl.id', '=', 'pd.portfolio_load_id')
            ->join('clients as c', 'c.id', '=', 'pd.client_id')
            ->leftJoin('advisors as a', 'a.id', '=', 'pd.advisor_id')
            ->leftJoin(DB::raw('(SELECT client_id, MAX(contact_date) as last_contact, MAX(id) as last_id FROM management_logs WHERE deleted_at IS NULL GROUP BY client_id) as last_ml'), 'last_ml.client_id', '=', 'c.id')
            ->leftJoin('management_logs as ml2', 'ml2.id', '=', 'last_ml.last_id')
            ->where('pl.is_active', true)
            ->where('pl.status', 'completed')
            ->whereIn('pd.status', ['active', 'partial', 'in_process'])
            ->whereNull('pd.deleted_at')
            ->select(
                'pd.id as doc_id', 'pd.document_number', 'pd.days_overdue', 'pd.pending_amount',
                'pd.due_date', 'pd.risk_level',
                'c.id as client_id', 'c.name as client_name', 'c.document_number as nit',
                'a.name as advisor_name',
                'last_ml.last_contact', 'ml2.type as last_type'
            )
            ->orderByRaw('CASE
                WHEN pd.days_overdue > 90 THEN 1
                WHEN pd.days_overdue > 60 THEN 2
                WHEN pd.days_overdue > 30 THEN 3
                WHEN pd.days_overdue > 0  THEN 4
                ELSE 5
            END')
            ->orderByDesc('pd.pending_amount');

        if ($this->advisorId) {
            $q->where('pd.advisor_id', $this->advisorId);
        }
        if ($this->search) {
            $q->where(fn ($sub) => $sub
                ->where('c.name', 'like', '%'.$this->search.'%')
                ->orWhere('c.document_number', 'like', '%'.$this->search.'%')
                ->orWhere('pd.document_number', 'like', '%'.$this->search.'%')
            );
        }
        if ($this->minBucket === '>90') {
            $q->where('pd.days_overdue', '>', 90);
        } elseif ($this->minBucket === '>60') {
            $q->where('pd.days_overdue', '>', 60);
        } elseif ($this->minBucket === '>30') {
            $q->where('pd.days_overdue', '>', 30);
        } elseif ($this->minBucket === '>0') {
            $q->where('pd.days_overdue', '>', 0);
        }

        return $q->limit(200)->get()->toArray();
    }

    public function applyFilter(): void
    {
        unset($this->items);
    }
}
