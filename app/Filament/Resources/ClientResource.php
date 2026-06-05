<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientResource\Pages;
use App\Models\Client;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-office-2';
    protected static string|\UnitEnum|null $navigationGroup = 'Operación';
    protected static ?string $navigationLabel = 'Clientes';
    protected static ?int $navigationSort = 4;
    protected static ?string $modelLabel = 'Cliente';
    protected static ?string $pluralModelLabel = 'Clientes';
    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $form): Schema
    {
        return $form->schema([
            Section::make('Información Principal')
                ->icon('heroicon-o-identification')
                ->columns(3)
                ->schema([
                    TextInput::make('code')
                        ->label('Código')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(50)
                        ->columnSpan(1),
                    TextInput::make('name')
                        ->label('Nombre / Razón Social')
                        ->required()
                        ->maxLength(255)
                        ->columnSpan(2),
                    Select::make('document_type')
                        ->label('Tipo de Documento')
                        ->options(['NIT' => 'NIT', 'CC' => 'Cédula', 'CE' => 'C. Extranjería'])
                        ->default('NIT')
                        ->required(),
                    TextInput::make('document_number')
                        ->label('Número de Documento')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(30),
                    Toggle::make('active')
                        ->label('Activo')
                        ->default(true)
                        ->inline(false),
                ]),

            Section::make('Contacto')
                ->icon('heroicon-o-phone')
                ->columns(2)
                ->collapsible()
                ->schema([
                    TextInput::make('email')->label('Correo')->email()->maxLength(255),
                    TextInput::make('phone')->label('Teléfono')->maxLength(30),
                    TextInput::make('address')->label('Dirección')->maxLength(255),
                    TextInput::make('city')->label('Ciudad')->maxLength(100),
                ]),

            Section::make('Clasificación Comercial')
                ->icon('heroicon-o-tag')
                ->columns(3)
                ->collapsible()
                ->schema([
                    TextInput::make('region')->label('Regional')->maxLength(100),
                    TextInput::make('channel')->label('Canal')->maxLength(100),
                    TextInput::make('uen')->label('UEN')->maxLength(100),
                    TextInput::make('credit_limit')
                        ->label('Cupo asignado')
                        ->numeric()
                        ->prefix('$')
                        ->helperText('Cupo del cliente en maestro SAP'),
                    TextInput::make('payment_term_days')
                        ->label('Plazo (días)')
                        ->numeric()
                        ->integer()
                        ->suffix('días')
                        ->helperText('Plazo contratado: vencimiento − fecha documento'),
                ]),

            Section::make('Contacto Responsable')
                ->icon('heroicon-o-user')
                ->columns(3)
                ->collapsible()
                ->schema([
                    TextInput::make('contact_name')->label('Nombre del Contacto')->maxLength(255),
                    TextInput::make('contact_email')->label('Correo del Contacto')->email()->maxLength(255),
                    TextInput::make('contact_phone')->label('Teléfono del Contacto')->maxLength(30),
                ]),

            Section::make('Observaciones')
                ->collapsible()
                ->schema([
                    Textarea::make('notes')->label('Notas')->rows(3),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')->label('Código')->searchable()->sortable()->copyable(),
                TextColumn::make('name')->label('Nombre')->searchable()->sortable()->weight('semibold'),
                TextColumn::make('document_number')->label('NIT / Doc.')->searchable()->copyable(),
                TextColumn::make('region')->label('Regional')->sortable()->badge()->color('info'),
                TextColumn::make('channel')->label('Canal')->sortable()->badge()->color('gray'),
                TextColumn::make('uen')->label('UEN')->sortable()->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('active')->label('Activo')->boolean(),
                TextColumn::make('portfolioDocuments_count')
                    ->label('Docs. Cartera')
                    ->counts('portfolioDocuments')
                    ->badge()
                    ->color('primary'),
                TextColumn::make('created_at')->label('Creado')->since()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('active')->label('Estado')->trueLabel('Activos')->falseLabel('Inactivos'),
                SelectFilter::make('region')->label('Regional')
                    ->options(fn () => Client::distinct()->whereNotNull('region')->pluck('region', 'region')),
                SelectFilter::make('channel')->label('Canal')
                    ->options(fn () => Client::distinct()->whereNotNull('channel')->pluck('channel', 'channel')),
                SelectFilter::make('uen')->label('UEN')
                    ->options(fn () => Client::distinct()->whereNotNull('uen')->pluck('uen', 'uen')),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ])
            ->defaultSort('name');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListClients::route('/'),
            'create' => Pages\CreateClient::route('/create'),
            'edit'   => Pages\EditClient::route('/{record}/edit'),
            'view'   => Pages\ViewClient::route('/{record}'),
        ];
    }
}
