<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AdvisorResource\Pages;
use App\Models\Advisor;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class AdvisorResource extends Resource
{
    protected static ?string $model = Advisor::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-group';
    protected static string|\UnitEnum|null $navigationGroup = 'Administración';
    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $navigationLabel = 'Asesores';
    protected static ?int $navigationSort = 2;
    protected static ?string $modelLabel = 'Asesor';
    protected static ?string $pluralModelLabel = 'Asesores';
    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $form): Schema
    {
        return $form->schema([
            Section::make()->columns(2)->schema([
                TextInput::make('code')->label('Código')->maxLength(50)->unique(ignoreRecord: true),
                TextInput::make('name')->label('Nombre Completo')->required()->maxLength(255),
                TextInput::make('email')->label('Correo')->email()->maxLength(255),
                TextInput::make('phone')->label('Teléfono')->maxLength(30),
                TextInput::make('region')->label('Regional')->maxLength(100),
                Select::make('user_id')
                    ->label('Usuario del Sistema')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->helperText('Vincular con cuenta del sistema'),
                Toggle::make('active')->label('Activo')->default(true)->inline(false),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')->label('Código')->searchable()->sortable(),
                TextColumn::make('name')->label('Nombre')->searchable()->sortable()->weight('semibold'),
                TextColumn::make('email')->label('Correo')->searchable()->toggleable(),
                TextColumn::make('region')->label('Regional')->badge()->color('info'),
                TextColumn::make('user.name')->label('Usuario')->toggleable(),
                IconColumn::make('active')->label('Activo')->boolean(),
                TextColumn::make('portfolioDocuments_count')
                    ->label('Docs. Asignados')
                    ->counts('portfolioDocuments')
                    ->badge()
                    ->color('primary'),
            ])
            ->filters([
                TernaryFilter::make('active')->label('Estado'),
            ])
            ->actions([ViewAction::make(), EditAction::make()])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListAdvisors::route('/'),
            'create' => Pages\CreateAdvisor::route('/create'),
            'edit'   => Pages\EditAdvisor::route('/{record}/edit'),
        ];
    }
}
