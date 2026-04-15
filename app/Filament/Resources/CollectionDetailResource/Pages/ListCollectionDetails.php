<?php

namespace App\Filament\Resources\CollectionDetailResource\Pages;

use App\Filament\Resources\CollectionDetailResource;
use Filament\Resources\Pages\ListRecords;

class ListCollectionDetails extends ListRecords
{
    protected static string $resource = CollectionDetailResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
