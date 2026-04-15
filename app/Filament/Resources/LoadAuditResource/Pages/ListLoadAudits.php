<?php

namespace App\Filament\Resources\LoadAuditResource\Pages;

use App\Filament\Resources\LoadAuditResource;
use Filament\Resources\Pages\ListRecords;

class ListLoadAudits extends ListRecords
{
    protected static string $resource = LoadAuditResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
