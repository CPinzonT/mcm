<?php

namespace App\Filament\Resources\ClientResource\Pages;

use App\Filament\Resources\ClientResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;

class ListClients extends ListRecords
{
    protected static string $resource = ClientResource::class;

    public function getSubheading(): string|Htmlable|null
    {
        $query = $this->getFilteredTableQuery();
        if ($query === null) {
            return null;
        }

        $count = $query->count();

        return number_format($count, 0, ',', '.') . ' ' . ($count === 1 ? 'cliente' : 'clientes');
    }

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
