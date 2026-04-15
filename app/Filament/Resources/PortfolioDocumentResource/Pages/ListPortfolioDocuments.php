<?php

namespace App\Filament\Resources\PortfolioDocumentResource\Pages;

use App\Filament\Resources\PortfolioDocumentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPortfolioDocuments extends ListRecords
{
    protected static string $resource = PortfolioDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
