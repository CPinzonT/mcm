<?php

namespace App\Filament\Resources\CastigoCaseResource\Pages;

use App\Filament\Resources\CastigoCaseResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCastigoCase extends EditRecord
{
    protected static string $resource = CastigoCaseResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
