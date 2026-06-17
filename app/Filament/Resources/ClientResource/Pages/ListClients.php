<?php

namespace App\Filament\Resources\ClientResource\Pages;

use App\Filament\Resources\ClientResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListClients extends ListRecords
{
    protected static string $resource = ClientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('actualizacionData')
                ->label('Actualización data')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->url(ClientResource::getUrl('import-contacts')),
            CreateAction::make(),
        ];
    }
}
