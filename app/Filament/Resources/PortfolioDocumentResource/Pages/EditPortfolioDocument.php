<?php

namespace App\Filament\Resources\PortfolioDocumentResource\Pages;

use App\Filament\Resources\PortfolioDocumentResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditPortfolioDocument extends EditRecord
{
    protected static string $resource = PortfolioDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [ViewAction::make(), DeleteAction::make()];
    }
}
