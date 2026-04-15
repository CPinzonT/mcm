<?php

namespace App\Filament\Resources\CollectionLoadResource\Pages;

use App\Filament\Resources\CollectionLoadResource;
use App\Services\Loads\LoadAuditService;
use App\Services\Loads\LoadDeletionService;
use App\Services\Loads\PeriodControlService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;

class ViewCollectionLoad extends ViewRecord
{
    protected static string $resource = CollectionLoadResource::class;

    public function getTitle(): string
    {
        return 'Detalle de carga de recaudos';
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            View::make('filament.resources.collection-load-resource.pages.view-collection-load')
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
                ->url(route('admin.loads.collection.source', $this->record))
                ->openUrlInNewTab(),
            Action::make('errores')
                ->label('Descargar errores')
                ->icon('heroicon-o-exclamation-circle')
                ->url(route('admin.loads.collection.errors', $this->record))
                ->openUrlInNewTab()
                ->visible(fn (): bool => $this->record->error_rows > 0),
            Action::make('anular')
                ->label('Anular carga')
                ->icon('heroicon-o-no-symbol')
                ->color('danger')
                ->visible(fn (): bool => $this->record->status === 'completed' && auth()->user()?->hasRole('admin'))
                ->requiresConfirmation()
                ->modalHeading('Anular carga de recaudos')
                ->modalDescription('Se desactivara esta version. Solo se permite si existe una version anterior del mismo periodo o si no compromete la cronologia.')
                ->action(function (PeriodControlService $periodControlService, LoadAuditService $auditService): void {
                    try {
                        $periodControlService->cancelCollectionLoad(
                            load: $this->record,
                            user: auth()->user(),
                            reason: 'Anulacion manual desde el panel administrativo.',
                        );

                        $auditService->record(
                            $this->record->fresh(),
                            'collection',
                            'cancelled',
                            'Carga de recaudos anulada desde el detalle.',
                            auth()->user(),
                        );

                        Notification::make()
                            ->title('Carga anulada')
                            ->success()
                            ->send();

                        $this->record = $this->record->fresh();
                    } catch (\Throwable $exception) {
                        Notification::make()
                            ->title('No se pudo anular la carga')
                            ->body($exception->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            Action::make('eliminar')
                ->label('Eliminar carga')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->visible(fn (): bool => auth()->user()?->can('delete', $this->record) ?? false)
                ->requiresConfirmation()
                ->modalHeading('Eliminar carga de recaudos')
                ->modalDescription('Se eliminara el registro, el archivo fuente y sus errores. Las cargas activas deben anularse primero para no afectar el periodo vigente.')
                ->action(function (LoadDeletionService $deletionService): void {
                    try {
                        $deletionService->deleteCollectionLoad(
                            load: $this->record,
                            user: auth()->user(),
                        );

                        Notification::make()
                            ->title('Carga eliminada')
                            ->body('La carga de recaudos y su archivo fuente fueron eliminados.')
                            ->success()
                            ->send();

                        $this->redirect(CollectionLoadResource::getUrl('index'));
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
