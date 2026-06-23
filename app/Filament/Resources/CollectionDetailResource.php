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
use Illuminate\Database\Eloquent\Builder;

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

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->forLatestActiveLoad()
            ->with(['portfolioDocument:id,due_date']);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
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
                TextColumn::make('portfolioDocument.due_date')
                    ->label('Fecha vencimiento')
                    ->date('d/m/Y')
                    ->placeholder('—'),
                TextColumn::make('payment_days_before_due')
                    ->label('Días anticipo')
                    ->alignEnd()
                    ->state(fn (CollectionDetail $record): ?string => self::formatPaymentDaysBeforeDue($record))
                    ->color(fn (CollectionDetail $record): string => $record->isEarlyPayment() ? 'success' : 'gray')
                    ->placeholder('—'),
                TextColumn::make('payment_timing')
                    ->label('Momento')
                    ->badge()
                    ->state(fn (CollectionDetail $record): ?string => self::paymentTimingLabel($record))
                    ->color(fn (?string $state): string => match ($state) {
                        'Anticipado'      => 'success',
                        'Al vencimiento'  => 'info',
                        'Pago tardío'     => 'warning',
                        default           => 'gray',
                    })
                    ->placeholder('Sin factura'),
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
                        ConciliationService::STATUS_CLIENT_MISMATCH => 'warning',
                        ConciliationService::STATUS_SELLER_MISMATCH => 'warning',
                        default                                      => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        ConciliationService::STATUS_MATCHED_FULL    => 'Conciliado total',
                        ConciliationService::STATUS_MATCHED_PARTIAL => 'Conciliado parcial',
                        ConciliationService::STATUS_OVERPAID        => 'Pago excedido',
                        ConciliationService::STATUS_NO_INVOICE      => 'Sin factura',
                        ConciliationService::STATUS_TYPE_MISMATCH   => 'Tipo diferente',
                        ConciliationService::STATUS_PERIOD_MISMATCH => 'Periodo diferente',
                        ConciliationService::STATUS_CLIENT_MISMATCH => 'Cliente diferente',
                        ConciliationService::STATUS_SELLER_MISMATCH => 'Vendedor diferente',
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
                SelectFilter::make('reconciliation_status')
                    ->label('Estado conciliación')
                    ->options([
                        ConciliationService::STATUS_MATCHED_FULL    => 'Conciliado total',
                        ConciliationService::STATUS_MATCHED_PARTIAL => 'Conciliado parcial',
                        ConciliationService::STATUS_OVERPAID        => 'Pago excedido',
                        ConciliationService::STATUS_NO_INVOICE      => 'Sin factura',
                        ConciliationService::STATUS_TYPE_MISMATCH   => 'Tipo diferente',
                        ConciliationService::STATUS_PERIOD_MISMATCH => 'Periodo diferente',
                        ConciliationService::STATUS_CLIENT_MISMATCH => 'Cliente diferente',
                        ConciliationService::STATUS_SELLER_MISMATCH => 'Vendedor diferente',
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
                    ->action(fn () => app(ExportService::class)->exportCollectionDetails()),
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

    private static function formatPaymentDaysBeforeDue(CollectionDetail $record): ?string
    {
        $days = $record->paymentDaysBeforeDue();

        if ($days === null) {
            return null;
        }

        if ($days > 0) {
            return number_format($days, 0, ',', '.');
        }

        if ($days === 0) {
            return '0';
        }

        return number_format($days, 0, ',', '.');
    }

    private static function paymentTimingLabel(CollectionDetail $record): ?string
    {
        $days = $record->paymentDaysBeforeDue();

        if ($days === null) {
            return null;
        }

        if ($days > 0) {
            return 'Anticipado';
        }

        if ($days === 0) {
            return 'Al vencimiento';
        }

        return 'Pago tardío';
    }
}
