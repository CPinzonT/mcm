<?php

namespace App\Filament\Resources\CastigoCaseResource\Pages;

use App\Filament\Resources\CastigoCaseResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCastigoCase extends ViewRecord
{
    protected static string $resource = CastigoCaseResource::class;

    protected function getHeaderActions(): array
    {
        return [EditAction::make()];
    }
}
