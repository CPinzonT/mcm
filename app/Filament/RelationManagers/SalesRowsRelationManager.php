<?php

namespace App\Filament\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class SalesRowsRelationManager extends RelationManager
{
    protected static string $relationship = 'rows';

    protected static ?string $title = 'Detalle de ventas';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return $ownerRecord->status === 'completed';
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('row_number')
                    ->label('Fila')
                    ->alignCenter(),
                TextColumn::make('sale_date')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->placeholder('-'),
                TextColumn::make('document_number')
                    ->label('Documento')
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('client_name')
                    ->label('Cliente')
                    ->searchable()
                    ->wrap()
                    ->placeholder('-'),
                TextColumn::make('product_name')
                    ->label('Producto')
                    ->wrap()
                    ->placeholder('-'),
                TextColumn::make('quantity')
                    ->label('Cantidad')
                    ->alignRight()
                    ->placeholder('-'),
                TextColumn::make('sale_amount')
                    ->label('Valor')
                    ->money('COP')
                    ->alignRight(),
                TextColumn::make('seller_name')
                    ->label('Vendedor')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('uen')
                    ->label('UEN')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('channel')
                    ->label('Canal')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('row_number')
            ->paginated([10, 25, 50]);
    }
}
