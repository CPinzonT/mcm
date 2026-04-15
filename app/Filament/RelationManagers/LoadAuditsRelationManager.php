<?php

namespace App\Filament\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class LoadAuditsRelationManager extends RelationManager
{
    protected static string $relationship = 'audits';

    protected static ?string $title = 'Auditoria';

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
                TextColumn::make('action')
                    ->label('Evento')
                    ->badge(),
                TextColumn::make('description')
                    ->label('Descripcion')
                    ->wrap(),
                TextColumn::make('user.name')
                    ->label('Usuario')
                    ->placeholder('-'),
                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i'),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([5, 10, 25]);
    }
}
