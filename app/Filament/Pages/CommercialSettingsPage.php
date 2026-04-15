<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;

class CommercialSettingsPage extends Page
{
    protected string $view = 'filament.pages.commercial-settings';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-storefront';
    protected static string|\UnitEnum|null   $navigationGroup = 'Configuración';
    protected static ?string $navigationLabel = 'Parametrización comercial';
    protected static ?string $title  = 'Parametrización Comercial';
    protected static ?int    $navigationSort = 3;

    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable|null
    {
        return null;
    }

    #[Computed]
    public function uens(): array
    {
        return DB::table('clients')
            ->whereNotNull('uen')->where('uen', '!=', '')
            ->select('uen')->distinct()->orderBy('uen')
            ->pluck('uen')->toArray();
    }

    #[Computed]
    public function regionals(): array
    {
        return DB::table('clients')
            ->whereNotNull('region')->where('region', '!=', '')
            ->select('region')->distinct()->orderBy('region')
            ->pluck('region')->toArray();
    }

    #[Computed]
    public function channels(): array
    {
        return DB::table('clients')
            ->whereNotNull('channel')->where('channel', '!=', '')
            ->select('channel')->distinct()->orderBy('channel')
            ->pluck('channel')->toArray();
    }
}
