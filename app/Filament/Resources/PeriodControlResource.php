<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PeriodControlResource\Pages;
use App\Models\PeriodControl;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PeriodControlResource extends Resource
{
    protected static ?string $model = PeriodControl::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected static string|\UnitEnum|null $navigationGroup = 'Configuración';
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationLabel = 'Control de periodos';

    protected static ?int $navigationSort = 4;

    protected static ?string $modelLabel = 'Periodo';

    protected static ?string $pluralModelLabel = 'Control de periodos';

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('period_key', 'desc')
            ->columns([
                TextColumn::make('period_key')
                    ->label('Periodo')
                    ->badge()
                    ->sortable(),
                TextColumn::make('period_date')
                    ->label('Fecha')
                    ->date('M Y')
                    ->sortable(),
                TextColumn::make('portfolio_version')
                    ->label('Cartera v.')
                    ->placeholder('—')
                    ->badge()
                    ->color('info'),
                TextColumn::make('portfolio_loaded_at')
                    ->label('Cartera cargada')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('collection_version')
                    ->label('Recaudos v.')
                    ->placeholder('—')
                    ->badge()
                    ->color('success'),
                TextColumn::make('collection_loaded_at')
                    ->label('Recaudos cargados')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('—')
                    ->toggleable(),
                IconColumn::make('is_complete')
                    ->label('Completo')
                    ->state(fn (PeriodControl $record): bool => $record->portfolio_load_id !== null && $record->collection_load_id !== null)
                    ->boolean(),
            ])
            ->actions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPeriodControls::route('/'),
        ];
    }
}
