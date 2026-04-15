<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReportTemplateResource\Pages;
use App\Models\ReportTemplate;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ReportTemplateResource extends Resource
{
    protected static ?string $model = ReportTemplate::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static string|\UnitEnum|null $navigationGroup = 'Inteligencia';
    protected static ?string $navigationLabel = 'Plantillas de Informes';
    protected static ?int $navigationSort = 1;
    protected static ?string $modelLabel = 'Plantilla';
    protected static ?string $pluralModelLabel = 'Plantillas de Informes';

    /** Campos disponibles para incluir en un informe */
    public static function availableFields(): array
    {
        return [
            'period_key'           => 'Periodo (YYYY-MM)',
            'aging_bucket'         => 'Bucket Mora',
            'account'              => 'Cuenta',
            'client.name'          => 'Cliente',
            'client.document_number'=> 'NIT Cliente',
            'document_number'      => 'Número Documento',
            'document_type'        => 'Tipo Documento',
            'issue_date'           => 'Fecha Emisión',
            'due_date'             => 'Fecha Vencimiento',
            'period_date'          => 'Fecha de Corte',
            'original_amount'      => 'Monto Original',
            'pending_amount'       => 'Saldo Pendiente',
            'collected_amount'     => 'Recaudado',
            'days_overdue'         => 'Días de Mora',
            'risk_level'           => 'Riesgo',
            'status'               => 'Estado',
            'advisor.name'         => 'Asesor',
            'client.region'        => 'Regional',
            'client.channel'       => 'Canal',
            'client.uen'           => 'UEN',
        ];
    }

    public static function form(Schema $form): Schema
    {
        return $form->schema([
            Section::make('Configuración Base')
                ->columns(2)
                ->schema([
                    TextInput::make('name')->label('Nombre de la Plantilla')->required()->maxLength(255),
                    TextInput::make('slug')
                        ->label('Slug')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255)
                        ->helperText('Identificador único para URL'),
                    Select::make('type')
                        ->label('Tipo')
                        ->options(['base' => 'Plantilla Base', 'client' => 'Por Cliente'])
                        ->required()
                        ->live(),
                    Select::make('client_id')
                        ->label('Cliente')
                        ->relationship('client', 'name')
                        ->searchable()
                        ->preload()
                        ->nullable()
                        ->visible(fn ($get) => $get('type') === 'client'),
                    Select::make('branding_profile_id')
                        ->label('Perfil de Branding')
                        ->relationship('brandingProfile', 'name')
                        ->preload()
                        ->nullable(),
                    Toggle::make('active')->label('Activa')->default(true)->inline(false),
                ]),

            Section::make('Encabezado del Informe')
                ->columns(2)
                ->collapsible()
                ->schema([
                    TextInput::make('title')->label('Título')->maxLength(255),
                    TextInput::make('subtitle')->label('Subtítulo')->maxLength(255),
                    Toggle::make('show_logo')->label('Mostrar Logo')->default(true)->inline(false),
                    Toggle::make('show_header')->label('Mostrar Encabezado')->default(true)->inline(false),
                    Toggle::make('show_footer')->label('Mostrar Pie de Página')->default(true)->inline(false),
                    Toggle::make('show_page_numbers')->label('Numeración de Páginas')->default(true)->inline(false),
                ]),

            Section::make('Columnas del Informe')
                ->description('Define qué columnas incluir y en qué orden aparecerán.')
                ->schema([
                    Repeater::make('columns')
                        ->relationship()
                        ->label('')
                        ->columns(5)
                        ->schema([
                            Select::make('field_key')
                                ->label('Campo')
                                ->options(static::availableFields())
                                ->required(),
                            TextInput::make('label')
                                ->label('Etiqueta')
                                ->required()
                                ->maxLength(100),
                            Select::make('format')
                                ->label('Formato')
                                ->options(['text' => 'Texto', 'currency' => 'Moneda',
                                    'date' => 'Fecha', 'percentage' => 'Porcentaje',
                                    'integer' => 'Entero'])
                                ->default('text'),
                            TextInput::make('order')
                                ->label('Orden')
                                ->numeric()
                                ->default(0),
                            Toggle::make('visible')->label('Visible')->default(true)->inline(false),
                        ])
                        ->defaultItems(0)
                        ->addActionLabel('Agregar Columna')
                        ->orderColumn('order'),
                ]),

            Section::make('Descripción')
                ->collapsible()
                ->schema([
                    Textarea::make('description')->label('Descripción')->rows(2),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nombre')->searchable()->weight('semibold'),
                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn ($s) => $s === 'base' ? 'Base Corporativa' : 'Por Cliente')
                    ->color(fn ($s) => $s === 'base' ? 'primary' : 'info'),
                TextColumn::make('client.name')->label('Cliente')->placeholder('—'),
                TextColumn::make('brandingProfile.name')->label('Branding')->placeholder('—'),
                TextColumn::make('columns_count')
                    ->label('Columnas')
                    ->counts('columns')
                    ->badge()
                    ->color('gray'),
                IconColumn::make('active')->label('Activa')->boolean(),
                TextColumn::make('createdBy.name')->label('Creado por')->toggleable(),
                TextColumn::make('updated_at')->label('Actualizado')->since()->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')->label('Tipo')
                    ->options(['base' => 'Base Corporativa', 'client' => 'Por Cliente']),
            ])
            ->actions([ViewAction::make(), EditAction::make()])
            ->defaultSort('updated_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListReportTemplates::route('/'),
            'create' => Pages\CreateReportTemplate::route('/create'),
            'edit'   => Pages\EditReportTemplate::route('/{record}/edit'),
            'view'   => Pages\ViewReportTemplate::route('/{record}'),
        ];
    }
}
