<?php

namespace App\Filament\Resources\CastigoCaseResource\Pages;

use App\Filament\Resources\CastigoCaseResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCastigoCases extends ListRecords
{
    protected static string $resource = CastigoCaseResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Nuevo Caso')];
    }
}
