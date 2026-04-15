<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BrandingProfileResource\Pages;
use App\Models\BrandingProfile;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BrandingProfileResource extends Resource
{
    protected static ?string $model = BrandingProfile::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-paint-brush';
    protected static string|\UnitEnum|null $navigationGroup = 'Configuración';
    protected static ?string $navigationLabel = 'Perfiles de Branding';
    protected static ?int $navigationSort = 3;
    protected static ?string $modelLabel = 'Perfil de Branding';
    protected static ?string $pluralModelLabel = 'Perfiles de Branding';

    public static function form(Schema $form): Schema
    {
        return $form->schema([
            Section::make('Identidad Corporativa')
                ->columns(2)
                ->schema([
                    TextInput::make('name')->label('Nombre del Perfil')->required()->maxLength(255),
                    TextInput::make('company_name')->label('Nombre de la Empresa')->required()->maxLength(255),
                    Toggle::make('is_default')
                        ->label('Perfil por Defecto')
                        ->helperText('Solo un perfil puede ser el predeterminado')
                        ->inline(false),
                    FileUpload::make('logo_path')
                        ->label('Logo')
                        ->image()
                        ->disk('public')
                        ->directory('branding/logos')
                        ->imagePreviewHeight('80'),
                ]),

            Section::make('Colores Institucionales')
                ->columns(3)
                ->schema([
                    ColorPicker::make('primary_color')->label('Color Primario')->default('#1e3a5f'),
                    ColorPicker::make('secondary_color')->label('Color Secundario')->default('#2563eb'),
                    ColorPicker::make('accent_color')->label('Color de Acento')->default('#f59e0b'),
                    Select::make('font_family')
                        ->label('Tipografía')
                        ->options(['Inter' => 'Inter', 'Roboto' => 'Roboto', 'Open Sans' => 'Open Sans',
                            'Lato' => 'Lato', 'Montserrat' => 'Montserrat'])
                        ->default('Inter'),
                ]),

            Section::make('Encabezado y Pie')
                ->columns(2)
                ->collapsible()
                ->schema([
                    Textarea::make('header_text')->label('Texto del Encabezado')->rows(2),
                    Textarea::make('footer_text')->label('Pie de Página')->rows(2),
                    TextInput::make('address')->label('Dirección')->maxLength(255),
                    TextInput::make('phone')->label('Teléfono')->maxLength(50),
                    TextInput::make('email')->label('Correo')->email()->maxLength(255),
                    TextInput::make('website')->label('Sitio Web')->url()->maxLength(255),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Perfil')->searchable()->weight('semibold'),
                TextColumn::make('company_name')->label('Empresa'),
                ColorColumn::make('primary_color')->label('Color Principal'),
                ColorColumn::make('secondary_color')->label('Color Secundario'),
                IconColumn::make('is_default')->label('Por Defecto')->boolean(),
            ])
            ->actions([EditAction::make()])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListBrandingProfiles::route('/'),
            'create' => Pages\CreateBrandingProfile::route('/create'),
            'edit'   => Pages\EditBrandingProfile::route('/{record}/edit'),
        ];
    }
}
