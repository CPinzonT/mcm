<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RiskLevelSettingResource\Pages;
use App\Models\RiskLevelSetting;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\EditAction;

class RiskLevelSettingResource extends Resource
{
    protected static ?string $model = RiskLevelSetting::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shield-exclamation';

    protected static string|\UnitEnum|null $navigationGroup = 'Configuración';

    protected static ?string $navigationLabel = 'Niveles de riesgo';

    protected static ?int $navigationSort = 5;

    protected static ?string $modelLabel = 'Nivel de riesgo';

    protected static ?string $pluralModelLabel = 'Niveles de riesgo';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('level')->label('Clave')->required()->maxLength(30),
            TextInput::make('label')->label('Etiqueta')->required()->maxLength(80),
            TextInput::make('days_min')->label('Días mínimo')->numeric()->required(),
            TextInput::make('days_max')->label('Días máximo')->numeric()->nullable(),
            ColorPicker::make('color')->label('Color')->nullable(),
            TextInput::make('badge_color')->label('Color badge Filament')->nullable()->helperText('primary, success, warning, danger, info, gray'),
            TextInput::make('order')->label('Orden')->numeric()->default(0),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('order')
            ->columns([
                TextColumn::make('order')->label('#')->sortable(),
                TextColumn::make('level')->label('Clave')->badge()->color(fn ($record) => $record->badge_color ?? 'gray'),
                TextColumn::make('label')->label('Etiqueta'),
                TextColumn::make('days_min')->label('Días mín.'),
                TextColumn::make('days_max')->label('Días máx.')->placeholder('∞'),
                ColorColumn::make('color')->label('Color')->toggleable(),
            ])
            ->actions([EditAction::make()]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListRiskLevelSettings::route('/'),
            'edit'   => Pages\EditRiskLevelSetting::route('/{record}/edit'),
        ];
    }
}
