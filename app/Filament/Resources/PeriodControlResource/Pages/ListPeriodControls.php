<?php

namespace App\Filament\Resources\PeriodControlResource\Pages;

use App\Filament\Resources\PeriodControlResource;
use Filament\Resources\Pages\ListRecords;

class ListPeriodControls extends ListRecords
{
    protected static string $resource = PeriodControlResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
