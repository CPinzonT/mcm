<?php

namespace App\Filament\Resources;

use App\Filament\RelationManagers\LoadAuditsRelationManager;
use App\Filament\RelationManagers\SalesRowsRelationManager;
use App\Filament\Resources\SalesLoadResource\Pages;
use App\Models\SalesLoad;
use App\Services\Loads\LoadDeletionService;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SalesLoadResource extends Resource
{
    protected static ?string $model = SalesLoad::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shopping-cart';

    protected static string|\UnitEnum|null $navigationGroup = 'Operación';

    protected static ?string $navigationLabel = 'Ventas';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'Carga de ventas';

    protected static ?string $pluralModelLabel = 'Cargas de ventas';

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference')
                    ->label('Referencia')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('original_filename')
                    ->label('Archivo')
                    ->limit(36)
                    ->tooltip(fn (SalesLoad $record): ?string => $record->original_filename)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('source_url')
                    ->label('Origen Azure')
                    ->limit(40)
                    ->tooltip(fn (SalesLoad $record): ?string => $record->source_url)
                    ->placeholder('Manual')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('period_key')
                    ->label('Periodo')
                    ->placeholder('-')
                    ->badge(),
                TextColumn::make('processed_rows')
                    ->label('Validas')
                    ->alignCenter(),
                TextColumn::make('error_rows')
                    ->label('Errores')
                    ->alignCenter()
                    ->badge()
                    ->color('danger'),
                TextColumn::make('total_amount')
                    ->label('Valor cargado')
                    ->money('COP')
                    ->alignRight(),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Pendiente',
                        'processing' => 'Procesando',
                        'completed' => 'Completada',
                        'rejected' => 'Rechazada',
                        'failed' => 'Fallida',
                        default => str($state)->headline()->value(),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'completed' => 'success',
                        'rejected', 'failed' => 'danger',
                        'processing' => 'warning',
                        default => 'info',
                    }),
                TextColumn::make('uploadedBy.name')
                    ->label('Usuario')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Creada')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'pending' => 'Pendiente',
                        'processing' => 'Procesando',
                        'completed' => 'Completada',
                        'rejected' => 'Rechazada',
                        'failed' => 'Fallida',
                    ]),
            ])
            ->actions([
                ViewAction::make()->label('Detalle'),
                Action::make('errores')
                    ->label('Errores')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn (SalesLoad $record): string => route('admin.loads.sales.errors', $record))
                    ->openUrlInNewTab()
                    ->visible(fn (SalesLoad $record): bool => $record->error_rows > 0),
                Action::make('eliminar')
                    ->label('Eliminar')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (SalesLoad $record): bool => auth()->user()?->can('delete', $record) ?? false)
                    ->modalHeading('Eliminar carga de ventas')
                    ->modalDescription('Elimina el registro, su archivo fuente y las filas asociadas.')
                    ->action(function (SalesLoad $record): void {
                        try {
                            app(LoadDeletionService::class)->deleteSalesLoad(
                                load: $record,
                                user: auth()->user(),
                            );

                            Notification::make()
                                ->title('Carga eliminada')
                                ->body('La carga de ventas y su archivo fuente fueron eliminados.')
                                ->success()
                                ->send();
                        } catch (\Throwable $exception) {
                            Notification::make()
                                ->title('No se pudo eliminar la carga')
                                ->body($exception->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            SalesRowsRelationManager::class,
            LoadAuditsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSalesLoads::route('/'),
            'view' => Pages\ViewSalesLoad::route('/{record}'),
        ];
    }
}
