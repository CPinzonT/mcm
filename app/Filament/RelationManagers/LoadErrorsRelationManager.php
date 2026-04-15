<?php

namespace App\Filament\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class LoadErrorsRelationManager extends RelationManager
{
    protected static string $relationship = 'errors';

    protected static ?string $title = 'Errores Guardados';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        if (! method_exists($ownerRecord, static::$relationship)) {
            return false;
        }

        return Schema::hasTable($ownerRecord->{static::$relationship}()->getRelated()->getTable());
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('row_number')
                    ->label('Fila')
                    ->alignCenter()
                    ->placeholder('-'),
                TextColumn::make('field')
                    ->label('Campo')
                    ->placeholder('-'),
                TextColumn::make('error_code')
                    ->label('Codigo')
                    ->badge()
                    ->placeholder('-'),
                TextColumn::make('message')
                    ->label('Mensaje')
                    ->wrap(),
                TextColumn::make('created_at')
                    ->label('Registrado')
                    ->since(),
            ])
            ->defaultSort('row_number')
            ->paginated([10, 25, 50]);
    }
}
