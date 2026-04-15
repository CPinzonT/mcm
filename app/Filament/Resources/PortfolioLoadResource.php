<?php

namespace App\Filament\Resources;

use App\Filament\RelationManagers\LoadAuditsRelationManager;
use App\Filament\RelationManagers\LoadErrorsRelationManager;
use App\Filament\RelationManagers\PortfolioDocumentsRelationManager;
use App\Filament\Resources\PortfolioLoadResource\Pages;
use App\Models\PortfolioLoad;
use App\Services\Loads\LoadAuditService;
use App\Services\Loads\LoadDeletionService;
use App\Services\Loads\PeriodControlService;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PortfolioLoadResource extends Resource
{
    protected static ?string $model = PortfolioLoad::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrow-up-tray';

    protected static string|\UnitEnum|null $navigationGroup = 'Operación';

    protected static ?string $navigationLabel = 'Cargas';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'Carga de cartera';

    protected static ?string $pluralModelLabel = 'Cargas de cartera';

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
                    ->tooltip(fn (PortfolioLoad $record): ?string => $record->original_filename)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('period_key')
                    ->label('Periodo')
                    ->placeholder('-')
                    ->badge(),
                TextColumn::make('version')
                    ->label('Version')
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('processed_rows')
                    ->label('Validas')
                    ->alignCenter(),
                TextColumn::make('error_rows')
                    ->label('Errores')
                    ->alignCenter()
                    ->badge()
                    ->color('danger'),
                TextColumn::make('total_pending_amount')
                    ->label('Saldo cargado')
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
                        'cancelled' => 'Anulada',
                        default => str($state)->headline()->value(),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'completed' => 'success',
                        'rejected', 'failed' => 'danger',
                        'cancelled' => 'gray',
                        'processing' => 'warning',
                        default => 'info',
                    }),
                IconColumn::make('is_active')
                    ->label('Activa')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                        'cancelled' => 'Anulada',
                    ]),
            ])
            ->actions([
                ViewAction::make()->label('Detalle'),
                Action::make('errores')
                    ->label('Errores')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn (PortfolioLoad $record): string => route('admin.loads.portfolio.errors', $record))
                    ->openUrlInNewTab()
                    ->visible(fn (PortfolioLoad $record): bool => $record->error_rows > 0),
                Action::make('anular')
                    ->label('Anular')
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (PortfolioLoad $record): bool => $record->status === 'completed' && auth()->user()?->hasRole('admin'))
                    ->action(function (PortfolioLoad $record): void {
                        try {
                            app(PeriodControlService::class)->cancelPortfolioLoad(
                                load: $record,
                                user: auth()->user(),
                                reason: 'Anulacion manual desde historial.',
                            );

                            app(LoadAuditService::class)->record(
                                $record->fresh(),
                                'portfolio',
                                'cancelled',
                                'Carga de cartera anulada desde historial.',
                                auth()->user(),
                            );

                            Notification::make()
                                ->title('Carga anulada')
                                ->success()
                                ->send();
                        } catch (\Throwable $exception) {
                            Notification::make()
                                ->title('No se pudo anular la carga')
                                ->body($exception->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                Action::make('eliminar')
                    ->label('Eliminar')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (PortfolioLoad $record): bool => auth()->user()?->can('delete', $record) ?? false)
                    ->modalHeading('Eliminar carga de cartera')
                    ->modalDescription('Elimina el registro, su archivo fuente y los errores asociados. Las cargas activas deben anularse primero y la eliminacion se bloquea si ya existen dependencias operativas.')
                    ->action(function (PortfolioLoad $record): void {
                        try {
                            app(LoadDeletionService::class)->deletePortfolioLoad(
                                load: $record,
                                user: auth()->user(),
                            );

                            Notification::make()
                                ->title('Carga eliminada')
                                ->body('La carga de cartera y su archivo fuente fueron eliminados.')
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
            PortfolioDocumentsRelationManager::class,
            LoadErrorsRelationManager::class,
            LoadAuditsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPortfolioLoads::route('/'),
            'view' => Pages\ViewPortfolioLoad::route('/{record}'),
        ];
    }
}
