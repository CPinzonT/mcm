<?php

namespace App\Filament\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class CollectionDetailsRelationManager extends RelationManager
{
    protected static string $relationship = 'details';

    protected static ?string $title = 'Detalle Insertado';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('row_number')
                    ->label('Fila')
                    ->alignCenter(),
                TextColumn::make('document_number')
                    ->label('Documento')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('client.name')
                    ->label('Cliente')
                    ->wrap(),
                TextColumn::make('amount')
                    ->label('Valor')
                    ->money('COP')
                    ->alignRight(),
                TextColumn::make('payment_date')
                    ->label('Fecha pago')
                    ->date('d/m/Y')
                    ->placeholder('-'),
                TextColumn::make('receipt_number')
                    ->label('Recibo')
                    ->placeholder('-'),
                TextColumn::make('notes')
                    ->label('Observacion')
                    ->wrap()
                    ->placeholder('-'),
            ])
            ->defaultSort('row_number')
            ->paginated([10, 25, 50]);
    }
}
