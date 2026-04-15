<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CollectionDetailResource\Pages;
use App\Models\CollectionDetail;
use App\Services\ConciliationService;
use App\Services\ExportService;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CollectionDetailResource extends Resource
{
    protected static ?string $model = CollectionDetail::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected static string|\UnitEnum|null $navigationGroup = 'Operación';

    protected static ?string $navigationLabel = 'Detalle recaudos';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'Detalle de recaudo';

    protected static ?string $pluralModelLabel = 'Detalles de recaudos';

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('period_key')
                    ->label('Periodo')
                    ->badge()
                    ->searchable(),
                TextColumn::make('document_number')
                    ->label('Documento')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('client_name')
                    ->label('Cliente')
                    ->limit(30)
                    ->searchable(),
                TextColumn::make('receipt_number')
                    ->label('Nro. recibo')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('amount')
                    ->label('Valor')
                    ->money('COP')
                    ->alignEnd(),
                TextColumn::make('payment_date')
                    ->label('Fecha pago')
                    ->date('d/m/Y'),
                TextColumn::make('reconciliation_status')
                    ->label('Estado conciliación')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        ConciliationService::STATUS_MATCHED_FULL    => 'success',
                        ConciliationService::STATUS_MATCHED_PARTIAL => 'warning',
                        ConciliationService::STATUS_OVERPAID        => 'danger',
                        ConciliationService::STATUS_NO_INVOICE      => 'gray',
                        ConciliationService::STATUS_TYPE_MISMATCH   => 'warning',
                        ConciliationService::STATUS_PERIOD_MISMATCH => 'info',
                        default                                      => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        ConciliationService::STATUS_MATCHED_FULL    => 'Conciliado total',
                        ConciliationService::STATUS_MATCHED_PARTIAL => 'Conciliado parcial',
                        ConciliationService::STATUS_OVERPAID        => 'Pago excedido',
                        ConciliationService::STATUS_NO_INVOICE      => 'Sin factura',
                        ConciliationService::STATUS_TYPE_MISMATCH   => 'Tipo diferente',
                        ConciliationService::STATUS_PERIOD_MISMATCH => 'Periodo diferente',
                        default                                      => 'Sin conciliar',
                    }),
                TextColumn::make('bucket')
                    ->label('Bucket')
                    ->badge()
                    ->color('info')
                    ->toggleable(),
                TextColumn::make('pending_amount_after')
                    ->label('Saldo tras pago')
                    ->money('COP')
                    ->alignEnd()
                    ->toggleable(),
                TextColumn::make('regional')
                    ->label('Regional')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('seller_name')
                    ->label('Vendedor')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('period_key')
                    ->label('Periodo')
                    ->options(fn () => CollectionDetail::query()
                        ->distinct()
                        ->orderByDesc('period_key')
                        ->pluck('period_key', 'period_key')
                        ->filter()
                        ->toArray()),
                SelectFilter::make('reconciliation_status')
                    ->label('Estado conciliación')
                    ->options([
                        ConciliationService::STATUS_MATCHED_FULL    => 'Conciliado total',
                        ConciliationService::STATUS_MATCHED_PARTIAL => 'Conciliado parcial',
                        ConciliationService::STATUS_OVERPAID        => 'Pago excedido',
                        ConciliationService::STATUS_NO_INVOICE      => 'Sin factura',
                        ConciliationService::STATUS_TYPE_MISMATCH   => 'Tipo diferente',
                        ConciliationService::STATUS_PERIOD_MISMATCH => 'Periodo diferente',
                    ]),
                SelectFilter::make('bucket')
                    ->label('Bucket mora')
                    ->options([
                        'corriente' => 'Corriente',
                        '1-30'      => '1-30 días',
                        '31-60'     => '31-60 días',
                        '61-90'     => '61-90 días',
                        '91-120'    => '91-120 días',
                        '121-180'   => '121-180 días',
                        '181-360'   => '181-360 días',
                        '+360'      => '+360 días',
                    ]),
            ])
            ->headerActions([
                Action::make('export')
                    ->label('Exportar XLSX')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->form([
                        \Filament\Forms\Components\Select::make('period_key')
                            ->label('Periodo')
                            ->options(fn () => CollectionDetail::query()
                                ->distinct()
                                ->orderByDesc('period_key')
                                ->pluck('period_key', 'period_key')
                                ->filter()
                                ->toArray())
                            ->required(),
                    ])
                    ->action(fn (array $data) => app(ExportService::class)->exportCollectionDetails($data['period_key'])),
            ])
            ->paginated([25, 50, 100]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCollectionDetails::route('/'),
        ];
    }
}
