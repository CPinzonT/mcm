<?php

namespace App\Filament\Resources;

use App\Filament\Pages\Dashboard;
use App\Filament\Resources\PortfolioDocumentResource\Pages;
use App\Models\PortfolioDocument;
use App\Models\ReportTemplate;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PortfolioDocumentResource extends Resource
{
    protected static ?string $model = PortfolioDocument::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';
    protected static string|\UnitEnum|null $navigationGroup = 'Operación';
    protected static ?string $navigationLabel = 'Cartera';
    protected static ?int $navigationSort = 3;
    protected static ?string $modelLabel = 'Documento';
    protected static ?string $pluralModelLabel = 'Documentos de Cartera';

    public static function form(Schema $form): Schema
    {
        return $form->schema([
            Section::make('Identificación')
                ->columns(3)
                ->schema([
                    Select::make('client_id')
                        ->label('Cliente')
                        ->relationship('client', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),
                    TextInput::make('document_number')
                        ->label('Número de Documento')
                        ->required()
                        ->maxLength(100),
                    Select::make('document_type')
                        ->label('Tipo')
                        ->options(['FACTURA' => 'Factura', 'NOTA' => 'Nota Débito', 'OTRO' => 'Otro'])
                        ->default('FACTURA')
                        ->required(),
                ]),

            Section::make('Fechas y Montos')
                ->columns(3)
                ->schema([
                    DatePicker::make('issue_date')->label('Fecha Emisión')->required(),
                    DatePicker::make('due_date')->label('Fecha Vencimiento')->required(),
                    DatePicker::make('period_date')->label('Fecha de Corte')->required(),
                    TextInput::make('original_amount')->label('Monto Original')->numeric()->prefix('$')->required(),
                    TextInput::make('pending_amount')->label('Saldo Pendiente')->numeric()->prefix('$')->required(),
                    TextInput::make('collected_amount')->label('Recaudado')->numeric()->prefix('$')->default(0),
                ]),

            Section::make('Clasificación')
                ->columns(3)
                ->schema([
                    TextInput::make('days_overdue')->label('Días de Mora')->numeric()->default(0),
                    Select::make('risk_level')
                        ->label('Riesgo de Mora')
                        ->options([
                            'normal'   => 'Normal (0-30 días)',
                            'low'      => 'Riesgo Bajo (31-60 días)',
                            'medium'   => 'Riesgo Medio (61-90 días)',
                            'high'     => 'Riesgo Alto (91-180 días)',
                            'critical' => 'Crítico (181+ días)',
                        ])
                        ->default('normal')
                        ->required(),
                    Select::make('status')
                        ->label('Estado')
                        ->options([
                            'active'     => 'Activo',
                            'partial'    => 'Parcialmente Pagado',
                            'paid'       => 'Pagado',
                            'written_off'=> 'Castigado',
                            'in_process' => 'En Proceso',
                        ])
                        ->default('active')
                        ->required(),
                    Select::make('advisor_id')
                        ->label('Asesor')
                        ->relationship('advisor', 'name')
                        ->searchable()
                        ->preload()
                        ->nullable(),
                    Select::make('currency')
                        ->label('Moneda')
                        ->options(['COP' => 'COP', 'USD' => 'USD'])
                        ->default('COP'),
                ]),

            Section::make('Observaciones')
                ->collapsible()
                ->schema([
                    Textarea::make('notes')->label('Notas')->rows(2),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('client.name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),
                TextColumn::make('document_number')
                    ->label('Documento')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('due_date')
                    ->label('Vencimiento')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('days_overdue')
                    ->label('Días Mora')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color(fn ($state) => match(true) {
                        $state <= 30  => 'success',
                        $state <= 60  => 'warning',
                        $state <= 90  => 'warning',
                        $state <= 180 => 'danger',
                        default       => 'danger',
                    }),
                TextColumn::make('risk_level')
                    ->label('Riesgo')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match($state) {
                        'normal'   => 'Normal',
                        'low'      => 'Bajo',
                        'medium'   => 'Medio',
                        'high'     => 'Alto',
                        'critical' => 'Crítico',
                        default    => $state,
                    })
                    ->color(fn ($state) => match($state) {
                        'normal'   => 'success',
                        'low'      => 'info',
                        'medium'   => 'warning',
                        'high'     => 'danger',
                        'critical' => 'danger',
                        default    => 'gray',
                    }),
                TextColumn::make('pending_amount')
                    ->label('Saldo Pendiente')
                    ->money('COP')
                    ->sortable()
                    ->alignRight(),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match($state) {
                        'active'      => 'Activo',
                        'partial'     => 'Parcial',
                        'paid'        => 'Pagado',
                        'written_off' => 'Castigado',
                        'in_process'  => 'En Proceso',
                        default       => $state,
                    })
                    ->color(fn ($state) => match($state) {
                        'active'      => 'primary',
                        'partial'     => 'warning',
                        'paid'        => 'success',
                        'written_off' => 'gray',
                        'in_process'  => 'info',
                        default       => 'gray',
                    }),
                TextColumn::make('advisor.name')
                    ->label('Asesor')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('period_date')
                    ->label('Corte')
                    ->date('M Y')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('risk_level')
                    ->label('Riesgo de Mora')
                    ->options([
                        'normal'   => 'Normal (0-30 días)',
                        'low'      => 'Riesgo Bajo (31-60 días)',
                        'medium'   => 'Riesgo Medio (61-90 días)',
                        'high'     => 'Riesgo Alto (91-180 días)',
                        'critical' => 'Crítico (181+ días)',
                    ]),
                SelectFilter::make('advisor_id')
                    ->label('Asesor')
                    ->relationship('advisor', 'name')
                    ->preload()
                    ->searchable(),
                SelectFilter::make('client_id')
                    ->label('Cliente')
                    ->relationship('client', 'name')
                    ->preload()
                    ->searchable(),
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'active'      => 'Activo',
                        'partial'     => 'Parcial',
                        'paid'        => 'Pagado',
                        'written_off' => 'Castigado',
                        'in_process'  => 'En Proceso',
                    ]),
                Filter::make('period_date')
                    ->form([DatePicker::make('period_date')->label('Fecha de Corte')])
                    ->query(fn (Builder $query, array $data) =>
                        $query->when($data['period_date'], fn ($q, $v) => $q->whereDate('period_date', $v))
                    ),
            ])
            ->headerActions([
                \Filament\Actions\Action::make('executive_dashboard')
                    ->label('Dashboard Ejecutivo')
                    ->icon('heroicon-o-home')
                    ->color('gray')
                    ->url(fn (): string => Dashboard::getUrl(panel: 'admin')),
                \Filament\Actions\Action::make('aging_export')
                    ->label('Exportar Aging XLSX')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->modalHeading('Exportar cartera en XLSX')
                    ->modalDescription('Selecciona el período y la plantilla de informe para construir el archivo.')
                    ->form([
                        \Filament\Forms\Components\Select::make('period_key')
                            ->label('Periodo (YYYY-MM)')
                            ->options(fn () => \App\Models\PortfolioDocument::query()
                                ->selectRaw('LEFT(period_date, 7) as pk')
                                ->whereNotNull('period_date')
                                ->groupBy('pk')
                                ->orderByDesc('pk')
                                ->pluck('pk', 'pk')
                                ->toArray())
                            ->required(),
                        \Filament\Forms\Components\Select::make('template_id')
                            ->label('Plantilla de informe')
                            ->options(fn () => ['0' => 'Plantilla estándar (Aging)'] +
                                ReportTemplate::query()
                                    ->with('client:id,name')
                                    ->where('active', true)
                                    ->where(function (Builder $query): void {
                                        $query->where('type', 'base')
                                            ->orWhere(fn (Builder $q) => $q->where('type', 'client')->whereNotNull('client_id'));
                                    })
                                    ->orderByRaw("CASE WHEN type = 'base' THEN 0 ELSE 1 END")
                                    ->orderBy('name')
                                    ->get()
                                    ->mapWithKeys(function (ReportTemplate $template): array {
                                        $suffix = $template->type === 'client'
                                            ? (' (Cliente: ' . ($template->client?->name ?? 'N/A') . ')')
                                            : ' (Base)';

                                        return [(string) $template->id => $template->name . $suffix];
                                    })
                                    ->toArray())
                            ->default('0')
                            ->searchable()
                            ->preload()
                            ->required(),
                    ])
                    ->action(fn (array $data) => app(\App\Services\ExportService::class)->exportAgingReport(
                        $data['period_key'],
                        ((int) ($data['template_id'] ?? 0)) > 0 ? (int) $data['template_id'] : null,
                    )),
            ])
            ->actions([ViewAction::make(), EditAction::make()])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])])
            ->defaultSort('days_overdue', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPortfolioDocuments::route('/'),
            'create' => Pages\CreatePortfolioDocument::route('/create'),
            'edit'   => Pages\EditPortfolioDocument::route('/{record}/edit'),
            'view'   => Pages\ViewPortfolioDocument::route('/{record}'),
        ];
    }
}
