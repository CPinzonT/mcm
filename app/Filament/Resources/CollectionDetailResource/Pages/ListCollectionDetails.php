<?php

namespace App\Filament\Resources\CollectionDetailResource\Pages;

use App\Filament\Resources\CollectionDetailResource;
use App\Models\CollectionDetail;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListCollectionDetails extends ListRecords
{
    protected static string $resource = CollectionDetailResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getSubheading(): ?string
    {
        $loadId = CollectionDetail::latestActiveCollectionLoadId();

        if ($loadId === null) {
            return 'Sin carga de recaudo activa.';
        }

        return 'Carga activa #' . $loadId . ' · Pago anticipado: fecha de pago anterior al vencimiento de la factura.';
    }

    public function getTabs(): array
    {
        $counts = $this->tabCounts();

        return [
            'all' => Tab::make('Todos')
                ->badge($counts['all']),
            'early' => Tab::make('Pagos anticipados')
                ->badge($counts['early'])
                ->modifyQueryUsing(fn (Builder $query) => $query->earlyPayment()),
        ];
    }

    /**
     * @return array{all: int, early: int}
     */
    private function tabCounts(): array
    {
        $base = CollectionDetail::query()->forLatestActiveLoad();

        return [
            'all'   => (clone $base)->count(),
            'early' => (clone $base)->earlyPayment()->count(),
        ];
    }
}
