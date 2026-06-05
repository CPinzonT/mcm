<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CollectionReconciliationResource\Pages;
use App\Models\CollectionReconciliation;
use App\Services\ConciliationService;
use App\Services\ExportService;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CollectionReconciliationResource extends Resource
{
    protected static ?string $model = CollectionReconciliation::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static string|\UnitEnum|null $navigationGroup = 'Operación';
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationLabel = 'Conciliación';

    protected static ?int $navigationSort = 4;

    protected static ?string $modelLabel = 'Registro de conciliación';

    protected static ?string $pluralModelLabel = 'Conciliación cartera-recaudos';

    public static function form(Schema $schema): Schema
    {
        return $schema;
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
                TextColumn::make('client_collection')
                    ->label('Cliente recaudo')
                    ->limit(28)
                    ->searchable(),
                TextColumn::make('client_portfolio')
                    ->label('Cliente cartera')
                    ->limit(28)
                    ->toggleable(),
                TextColumn::make('applied_amount')
                    ->label('Importe aplicado')
                    ->money('COP')
                    ->alignEnd(),
                TextColumn::make('portfolio_pending')
                    ->label('Saldo cartera')
                    ->money('COP')
                    ->alignEnd(),
                TextColumn::make('resulting_balance')
                    ->label('Saldo resultante')
                    ->money('COP')
                    ->alignEnd(),
                TextColumn::make('status')
                    ->label('Estado')
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
                TextColumn::make('confidence_level')
                    ->label('Confianza')
                    ->suffix('%')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('reconciled_at')
                    ->label('Conciliado')
                    ->dateTime('d/m/Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
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
            ])
            ->headerActions([
                Action::make('export')
                    ->label('Exportar XLSX')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(fn () => app(ExportService::class)->exportReconciliation()),
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
            'index' => Pages\ListCollectionReconciliations::route('/'),
        ];
    }
}
