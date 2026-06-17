<?php

namespace App\Filament\Resources\SalesLoadResource\Pages;

use App\Filament\Resources\SalesLoadResource;
use App\Services\Loads\LoadDeletionService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;

class ViewSalesLoad extends ViewRecord
{
    protected static string $resource = SalesLoadResource::class;

    public function getTitle(): string
    {
        return 'Detalle de carga de ventas';
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            View::make('filament.resources.sales-load-resource.pages.view-sales-load')
                ->viewData([
                    'page' => $this,
                    'record' => $this->record,
                ]),
            $this->getRelationManagersContentComponent(),
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('archivo')
                ->label('Descargar archivo')
                ->icon('heroicon-o-arrow-down-tray')
                ->url(route('admin.loads.sales.source', $this->record))
                ->openUrlInNewTab()
                ->visible(fn (): bool => filled($this->record->path)),
            Action::make('errores')
                ->label('Descargar errores')
                ->icon('heroicon-o-exclamation-circle')
                ->url(route('admin.loads.sales.errors', $this->record))
                ->openUrlInNewTab()
                ->visible(fn (): bool => $this->record->error_rows > 0),
            Action::make('eliminar')
                ->label('Eliminar carga')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->visible(fn (): bool => auth()->user()?->can('delete', $this->record) ?? false)
                ->requiresConfirmation()
                ->modalHeading('Eliminar carga de ventas')
                ->modalDescription('Se eliminará el registro, el archivo fuente y las filas importadas.')
                ->action(function (LoadDeletionService $deletionService): void {
                    try {
                        $deletionService->deleteSalesLoad(
                            load: $this->record,
                            user: auth()->user(),
                        );

                        Notification::make()
                            ->title('Carga eliminada')
                            ->body('La carga de ventas y su archivo fuente fueron eliminados.')
                            ->success()
                            ->send();

                        $this->redirect(SalesLoadResource::getUrl('index'));
                    } catch (\Throwable $exception) {
                        Notification::make()
                            ->title('No se pudo eliminar la carga')
                            ->body($exception->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
