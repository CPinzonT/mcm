<?php

namespace App\Filament\Resources\ReportTemplateResource\Pages;

use App\Filament\Resources\ReportTemplateResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewReportTemplate extends ViewRecord
{
    protected static string $resource = ReportTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [EditAction::make()];
    }
}
