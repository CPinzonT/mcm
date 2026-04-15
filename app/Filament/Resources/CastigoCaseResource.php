<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CastigoCaseResource\Pages;
use App\Models\CastigoCase;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CastigoCaseResource extends Resource
{
    protected static ?string $model = CastigoCase::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-exclamation-triangle';
    protected static string|\UnitEnum|null $navigationGroup = 'Configuración';
    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $navigationLabel = 'Casos de Castigo';
    protected static ?int $navigationSort = 1;
    protected static ?string $modelLabel = 'Caso de Castigo';
    protected static ?string $pluralModelLabel = 'Casos de Castigo';

    public static function form(Schema $form): Schema
    {
        return $form->schema([
            Section::make('Información del Caso')
                ->columns(3)
                ->schema([
                    TextInput::make('case_number')
                        ->label('Número de Caso')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(50)
                        ->placeholder('CAS-2026-001'),
                    Select::make('client_id')
                        ->label('Cliente')
                        ->relationship('client', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),
                    DatePicker::make('case_date')->label('Fecha del Caso')->required(),
                    TextInput::make('total_amount')
                        ->label('Monto Total del Castigo')
                        ->numeric()
                        ->prefix('$')
                        ->required(),
                    Select::make('status')
                        ->label('Estado')
                        ->options([
                            'draft'          => 'Borrador',
                            'in_review'      => 'En Revisión',
                            'approved'       => 'Aprobado',
                            'rejected'       => 'Rechazado',
                            'submitted_dian' => 'Radicado DIAN',
                            'closed'         => 'Cerrado',
                        ])
                        ->default('draft')
                        ->required(),
                    DatePicker::make('submitted_at')->label('Fecha Radicación DIAN')->nullable(),
                ]),

            Section::make('Descripción')
                ->schema([
                    Textarea::make('description')->label('Descripción del Caso')->rows(3),
                    Textarea::make('rejection_reason')
                        ->label('Motivo de Rechazo')
                        ->rows(2)
                        ->visible(fn ($get) => $get('status') === 'rejected'),
                ]),

            Section::make('Documentos Soporte')
                ->description('Adjunte los soportes documentales requeridos para el proceso ante la DIAN.')
                ->schema([
                    FileUpload::make('support_documents')
                        ->label('Adjuntos')
                        ->multiple()
                        ->disk('local')
                        ->directory('castigo-support')
                        ->acceptedFileTypes([
                            'application/pdf',
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/msword',
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                            'image/jpeg', 'image/png',
                        ])
                        ->maxFiles(20)
                        ->helperText('PDF, Excel, Word e imágenes. Máximo 20 archivos.')
                        ->storeFileNamesIn('support_document_names'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('case_number')
                    ->label('Caso')
                    ->searchable()
                    ->copyable()
                    ->weight('semibold'),
                TextColumn::make('client.name')->label('Cliente')->searchable()->sortable(),
                TextColumn::make('case_date')->label('Fecha')->date('d/m/Y')->sortable(),
                TextColumn::make('total_amount')->label('Monto')->money('COP')->alignRight()->sortable(),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn ($s) => match($s) {
                        'draft'          => 'Borrador',
                        'in_review'      => 'En Revisión',
                        'approved'       => 'Aprobado',
                        'rejected'       => 'Rechazado',
                        'submitted_dian' => 'Radicado DIAN',
                        'closed'         => 'Cerrado',
                        default          => $s,
                    })
                    ->color(fn ($s) => match($s) {
                        'draft'          => 'gray',
                        'in_review'      => 'warning',
                        'approved'       => 'success',
                        'rejected'       => 'danger',
                        'submitted_dian' => 'info',
                        'closed'         => 'primary',
                        default          => 'gray',
                    }),
                TextColumn::make('submitted_at')->label('Radicado DIAN')->date('d/m/Y')->placeholder('—'),
                TextColumn::make('createdBy.name')->label('Creado por')->toggleable(),
                TextColumn::make('approvedBy.name')->label('Aprobado por')->placeholder('—')->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')->label('Estado')->options([
                    'draft' => 'Borrador', 'in_review' => 'En Revisión',
                    'approved' => 'Aprobado', 'rejected' => 'Rechazado',
                    'submitted_dian' => 'Radicado DIAN', 'closed' => 'Cerrado',
                ]),
                SelectFilter::make('client_id')->label('Cliente')
                    ->relationship('client', 'name')->searchable()->preload(),
            ])
            ->actions([ViewAction::make(), EditAction::make()])
            ->defaultSort('case_date', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCastigoCases::route('/'),
            'create' => Pages\CreateCastigoCase::route('/create'),
            'edit'   => Pages\EditCastigoCase::route('/{record}/edit'),
            'view'   => Pages\ViewCastigoCase::route('/{record}'),
        ];
    }
}
