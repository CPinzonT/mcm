<?php

namespace App\Filament\Resources\RiskLevelSettingResource\Pages;

use App\Filament\Resources\RiskLevelSettingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRiskLevelSettings extends ListRecords
{
    protected static string $resource = RiskLevelSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
