<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BudgetLoadResource\Pages;
use App\Models\BudgetLoad;
use App\Services\Loads\LoadDeletionService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class BudgetLoadResource extends Resource
{
    protected static ?string $model = BudgetLoad::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected static string|\UnitEnum|null $navigationGroup = 'Operación';

    protected static ?string $navigationLabel = 'Presupuesto';

    protected static ?int $navigationSort = 4;

    protected static ?string $modelLabel = 'Carga de presupuesto';

    protected static ?string $pluralModelLabel = 'Cargas de presupuesto';

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
                    ->tooltip(fn (BudgetLoad $record): ?string => $record->original_filename)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('period_key')
                    ->label('Período')
                    ->placeholder('-')
                    ->badge(),
                TextColumn::make('valid_rows')
                    ->label('Filas válidas')
                    ->alignCenter(),
                TextColumn::make('error_rows')
                    ->label('Advertencias')
                    ->alignCenter()
                    ->badge()
                    ->color('warning'),
                TextColumn::make('total_amount')
                    ->label('PPTO total')
                    ->money('COP')
                    ->alignRight(),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'completed' => 'Completada',
                        'cancelled' => 'Anulada',
                        'failed'    => 'Fallida',
                        default     => str($state)->headline()->value(),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'completed' => 'success',
                        'cancelled' => 'gray',
                        'failed'    => 'danger',
                        default     => 'info',
                    }),
                TextColumn::make('uploader.name')
                    ->label('Usuario')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('processed_at')
                    ->label('Procesada')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Creada')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'completed' => 'Completada',
                        'cancelled' => 'Anulada',
                        'failed'    => 'Fallida',
                    ]),
            ])
            ->actions([
                Action::make('anular')
                    ->label('Anular')
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (BudgetLoad $record): bool => $record->status === 'completed' && auth()->user()?->hasRole('admin'))
                    ->modalHeading('Anular carga de presupuesto')
                    ->modalDescription('Elimina las filas importadas de esta carga y marca el registro como anulado.')
                    ->action(function (BudgetLoad $record): void {
                        try {
                            app(LoadDeletionService::class)->cancelBudgetLoad($record, auth()->user());

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
                    ->visible(fn (BudgetLoad $record): bool => auth()->user()?->can('delete', $record) ?? false)
                    ->modalHeading('Eliminar carga de presupuesto')
                    ->modalDescription('Elimina el registro, el archivo fuente y todas las filas asociadas a esta carga.')
                    ->action(function (BudgetLoad $record): void {
                        try {
                            app(LoadDeletionService::class)->deleteBudgetLoad($record, auth()->user());

                            Notification::make()
                                ->title('Carga eliminada')
                                ->body('La carga de presupuesto y su archivo fueron eliminados.')
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBudgetLoads::route('/'),
        ];
    }
}
