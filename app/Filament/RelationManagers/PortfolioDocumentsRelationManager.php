<?php

namespace App\Filament\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class PortfolioDocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'portfolioDocuments';

    protected static ?string $title = 'Documentos del Corte';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('client.name')
                    ->label('Cliente')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('document_number')
                    ->label('Documento')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('document_type')
                    ->label('Tipo'),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge(),
                TextColumn::make('pending_amount')
                    ->label('Saldo')
                    ->money('COP')
                    ->alignRight(),
                TextColumn::make('days_overdue')
                    ->label('Mora')
                    ->alignCenter(),
                TextColumn::make('created_at')
                    ->label('Registrado')
                    ->since(),
            ])
            ->defaultSort('pending_amount', 'desc')
            ->paginated([10, 25, 50]);
    }
}
