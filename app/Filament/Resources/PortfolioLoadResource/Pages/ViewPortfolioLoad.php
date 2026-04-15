<?php

namespace App\Filament\Resources\PortfolioLoadResource\Pages;

use App\Filament\Resources\PortfolioLoadResource;
use App\Services\Loads\LoadAuditService;
use App\Services\Loads\LoadDeletionService;
use App\Services\Loads\PeriodControlService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;

class ViewPortfolioLoad extends ViewRecord
{
    protected static string $resource = PortfolioLoadResource::class;

    public function getTitle(): string
    {
        return 'Detalle de carga de cartera';
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            View::make('filament.resources.portfolio-load-resource.pages.view-portfolio-load')
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
                ->url(route('admin.loads.portfolio.source', $this->record))
                ->openUrlInNewTab(),
            Action::make('errores')
                ->label('Descargar errores')
                ->icon('heroicon-o-exclamation-circle')
                ->url(route('admin.loads.portfolio.errors', $this->record))
                ->openUrlInNewTab()
                ->visible(fn (): bool => $this->record->error_rows > 0),
            Action::make('anular')
                ->label('Anular carga')
                ->icon('heroicon-o-no-symbol')
                ->color('danger')
                ->visible(fn (): bool => $this->record->status === 'completed' && auth()->user()?->hasRole('admin'))
                ->requiresConfirmation()
                ->modalHeading('Anular carga de cartera')
                ->modalDescription('Se desactivara esta version. Solo se permite si existe una version anterior del mismo periodo o si no compromete la cronologia.')
                ->action(function (PeriodControlService $periodControlService, LoadAuditService $auditService): void {
                    try {
                        $periodControlService->cancelPortfolioLoad(
                            load: $this->record,
                            user: auth()->user(),
                            reason: 'Anulacion manual desde el panel administrativo.',
                        );

                        $auditService->record(
                            $this->record->fresh(),
                            'portfolio',
                            'cancelled',
                            'Carga de cartera anulada desde el detalle.',
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
                ->modalHeading('Eliminar carga de cartera')
                ->modalDescription('Se eliminara el registro, el archivo fuente y sus errores. Las cargas activas deben anularse primero y el sistema bloqueara la operacion si encuentra dependencias.')
                ->action(function (LoadDeletionService $deletionService): void {
                    try {
                        $deletionService->deletePortfolioLoad(
                            load: $this->record,
                            user: auth()->user(),
                        );

                        Notification::make()
                            ->title('Carga eliminada')
                            ->body('La carga de cartera y su archivo fuente fueron eliminados.')
                            ->success()
                            ->send();

                        $this->redirect(PortfolioLoadResource::getUrl('index'));
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
