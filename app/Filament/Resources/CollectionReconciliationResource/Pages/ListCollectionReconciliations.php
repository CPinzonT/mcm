<?php

namespace App\Filament\Resources\CollectionReconciliationResource\Pages;

use App\Filament\Resources\CollectionReconciliationResource;
use Filament\Resources\Pages\ListRecords;

class ListCollectionReconciliations extends ListRecords
{
    protected static string $resource = CollectionReconciliationResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
