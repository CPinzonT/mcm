<?php

namespace App\Services\Loads;

use App\Models\BudgetLoad;
use App\Models\BudgetRow;
use App\Models\CollectionLoad;
use App\Models\PeriodControl;
use App\Models\PortfolioDocument;
use App\Models\PortfolioLoad;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class LoadDeletionService
{
    public function __construct(
        private readonly LoadAuditService $auditService,
    ) {}

    public function deletePortfolioLoad(PortfolioLoad $load, User $user): void
    {
        $disk = $load->disk;
        $path = $load->path;

        DB::transaction(function () use ($load, $user): void {
            $documents = PortfolioDocument::withTrashed()
                ->where('portfolio_load_id', $load->id)
                ->withCount(['collectionDetails', 'managementLogs'])
                ->get();

            $this->assertPortfolioLoadCanBeDeleted($load, $documents);
            $this->preparePortfolioPeriodControlForDeletion($load);

            $this->auditService->record(
                $load,
                'portfolio',
                'deleted',
                'Carga de cartera eliminada permanentemente.',
                $user,
                [
                    'reference' => $load->reference,
                    'status' => $load->status,
                    'period_key' => $load->period_key,
                    'documents' => $documents->count(),
                ],
            );

            foreach ($documents as $document) {
                $document->forceDelete();
            }

            $load->delete();
        });

        $this->deleteStoredFile($disk, $path);
    }

    public function deleteBudgetLoad(BudgetLoad $load, User $user): void
    {
        $disk = $load->disk;
        $path = $load->path;

        DB::transaction(function () use ($load): void {
            BudgetRow::query()->where('budget_load_id', $load->id)->delete();
            $load->delete();
        });

        $this->deleteStoredFile($disk, $path);
    }

    public function cancelBudgetLoad(BudgetLoad $load, User $user): void
    {
        DB::transaction(function () use ($load): void {
            BudgetRow::query()->where('budget_load_id', $load->id)->delete();
            $load->forceFill(['status' => 'cancelled'])->save();
        });
    }

    public function deleteCollectionLoad(CollectionLoad $load, User $user): void
    {
        $disk = $load->disk;
        $path = $load->path;

        DB::transaction(function () use ($load, $user): void {
            $this->assertCollectionLoadCanBeDeleted($load);
            $this->prepareCollectionPeriodControlForDeletion($load);

            $load->details()->delete();
            $load->errors()->delete();

            $this->auditService->record(
                $load,
                'collection',
                'deleted',
                'Carga de recaudos eliminada permanentemente.',
                $user,
                [
                    'reference' => $load->reference,
                    'status' => $load->status,
                    'period_key' => $load->period_key,
                    'details' => $load->details()->count(),
                ],
            );

            $load->delete();
        });

        $this->deleteStoredFile($disk, $path);
    }

    private function assertPortfolioLoadCanBeDeleted(PortfolioLoad $load, $documents): void
    {
        if ($load->is_active && $this->hasLaterPortfolioPeriods($load)) {
            throw new DomainException('No se puede eliminar esta carga de cartera porque ya existen periodos posteriores.');
        }

        $documentsWithCollections = $documents->sum('collection_details_count');

        if ($documentsWithCollections > 0) {
            throw new DomainException('No se puede eliminar esta carga de cartera porque sus documentos ya tienen recaudos asociados.');
        }

        $documentsWithManagement = $documents->sum('management_logs_count');

        if ($documentsWithManagement > 0) {
            throw new DomainException('No se puede eliminar esta carga de cartera porque sus documentos ya tienen gestiones registradas.');
        }
    }

    private function assertCollectionLoadCanBeDeleted(CollectionLoad $load): void
    {
        if ($load->is_active && $load->status === 'completed') {
            throw new DomainException('Anule la carga activa antes de eliminarla.');
        }
    }

    private function preparePortfolioPeriodControlForDeletion(PortfolioLoad $load): void
    {
        $referencesPeriodControl = PeriodControl::query()
            ->where('portfolio_load_id', $load->id)
            ->exists();

        if (! $referencesPeriodControl && ! $load->is_active) {
            return;
        }

        $fallback = $this->findPortfolioFallback($load);

        if ($fallback) {
            $fallback->forceFill(['is_active' => true])->save();

            PeriodControl::query()->updateOrCreate(
                ['period_key' => $fallback->period_key],
                [
                    'period_date' => $fallback->period_date,
                    'portfolio_load_id' => $fallback->id,
                    'portfolio_version' => $fallback->version,
                    'portfolio_loaded_at' => $fallback->processed_at ?? now(),
                ],
            );

            return;
        }

        PeriodControl::query()
            ->where('portfolio_load_id', $load->id)
            ->update([
                'portfolio_load_id' => null,
                'portfolio_version' => 0,
                'portfolio_loaded_at' => null,
            ]);
    }

    private function prepareCollectionPeriodControlForDeletion(CollectionLoad $load): void
    {
        $referencesPeriodControl = PeriodControl::query()
            ->where('collection_load_id', $load->id)
            ->exists();

        if (! $referencesPeriodControl && ! $load->is_active) {
            return;
        }

        $fallback = $this->findCollectionFallback($load);

        if ($fallback) {
            $fallback->forceFill(['is_active' => true])->save();

            PeriodControl::query()->updateOrCreate(
                ['period_key' => $fallback->period_key],
                [
                    'period_date' => $fallback->period_date,
                    'collection_load_id' => $fallback->id,
                    'collection_version' => $fallback->version,
                    'collection_loaded_at' => $fallback->processed_at ?? now(),
                ],
            );

            return;
        }

        PeriodControl::query()
            ->where('collection_load_id', $load->id)
            ->update([
                'collection_load_id' => null,
                'collection_version' => 0,
                'collection_loaded_at' => null,
            ]);
    }

    private function findPortfolioFallback(PortfolioLoad $load): ?PortfolioLoad
    {
        if (blank($load->period_key)) {
            return null;
        }

        return PortfolioLoad::query()
            ->where('period_key', $load->period_key)
            ->where('status', 'completed')
            ->where('id', '!=', $load->id)
            ->orderByDesc('version')
            ->orderByDesc('id')
            ->first();
    }

    private function findCollectionFallback(CollectionLoad $load): ?CollectionLoad
    {
        if (blank($load->period_key)) {
            return null;
        }

        return CollectionLoad::query()
            ->where('period_key', $load->period_key)
            ->where('status', 'completed')
            ->where('id', '!=', $load->id)
            ->orderByDesc('version')
            ->orderByDesc('id')
            ->first();
    }

    private function hasLaterPortfolioPeriods(PortfolioLoad $load): bool
    {
        if (! $load->period_date) {
            return false;
        }

        return PortfolioLoad::query()
            ->where('status', 'completed')
            ->where('id', '!=', $load->id)
            ->whereDate('period_date', '>', $load->period_date)
            ->exists();
    }

    private function hasLaterCollectionPeriods(CollectionLoad $load): bool
    {
        if (! $load->period_date) {
            return false;
        }

        return CollectionLoad::query()
            ->where('status', 'completed')
            ->where('id', '!=', $load->id)
            ->whereDate('period_date', '>', $load->period_date)
            ->exists();
    }

    private function deleteStoredFile(?string $disk, ?string $path): void
    {
        if (! $disk || ! $path) {
            return;
        }

        if (! Storage::disk($disk)->exists($path)) {
            return;
        }

        Storage::disk($disk)->delete($path);
    }
}
