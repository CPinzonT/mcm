<?php

namespace App\Services\Loads;

use App\Models\CollectionLoad;
use App\Models\PeriodControl;
use App\Models\PortfolioLoad;
use App\Models\User;
use Carbon\CarbonImmutable;
use DomainException;
use Illuminate\Support\Facades\DB;

class PeriodControlService
{
    public function nextPortfolioVersion(CarbonImmutable $periodDate): int
    {
        return (int) PortfolioLoad::query()
            ->where('period_key', $periodDate->format('Y-m'))
            ->max('version') + 1;
    }

    public function nextCollectionVersion(CarbonImmutable $periodDate): int
    {
        return (int) CollectionLoad::query()
            ->where('period_key', $periodDate->format('Y-m'))
            ->max('version') + 1;
    }

    public function assertPortfolioChronology(CarbonImmutable $periodDate): void
    {
        $latest = PortfolioLoad::query()
            ->where('status', 'completed')
            ->whereNotNull('period_date')
            ->orderByDesc('period_date')
            ->orderByDesc('version')
            ->first();

        if (! $latest) {
            return;
        }

        $latestDate = CarbonImmutable::parse($latest->period_date)->startOfMonth();

        if ($periodDate->startOfMonth()->lt($latestDate)) {
            throw new DomainException(
                "No se puede cargar cartera del periodo {$periodDate->format('Y-m')} porque existe una carga valida mas reciente ({$latestDate->format('Y-m')})."
            );
        }
    }

    public function assertCollectionChronology(CarbonImmutable $periodDate): void
    {
        $latest = CollectionLoad::query()
            ->where('status', 'completed')
            ->whereNotNull('period_date')
            ->orderByDesc('period_date')
            ->orderByDesc('version')
            ->first();

        if (! $latest) {
            return;
        }

        $latestDate = CarbonImmutable::parse($latest->period_date)->startOfMonth();

        if ($periodDate->startOfMonth()->lt($latestDate)) {
            throw new DomainException(
                "No se puede cargar recaudos del periodo {$periodDate->format('Y-m')} porque existe una version valida mas reciente ({$latestDate->format('Y-m')})."
            );
        }
    }

    public function assertCollectionPortfolioPrerequisite(CarbonImmutable $periodDate): void
    {
        $periodKey = $periodDate->format('Y-m');

        $hasPortfolio = PortfolioLoad::query()
            ->where('period_key', $periodKey)
            ->where('status', 'completed')
            ->where('is_active', true)
            ->exists();

        if (! $hasPortfolio) {
            throw new DomainException(
                "Antes de cargar recaudos del periodo {$periodKey} debe existir una carga de cartera activa para el mismo periodo."
            );
        }
    }

    public function activatePortfolioLoad(PortfolioLoad $load): void
    {
        $this->deactivateSamePeriodPortfolioLoads($load);

        $load->forceFill([
            'is_active' => true,
        ])->save();

        $this->syncPeriodControlForPortfolio($load);
    }

    public function activateCollectionLoad(CollectionLoad $load): void
    {
        $this->deactivateSamePeriodCollectionLoads($load);

        $load->forceFill([
            'is_active' => true,
        ])->save();

        $this->syncPeriodControlForCollection($load);
    }

    public function cancelPortfolioLoad(PortfolioLoad $load, User $user, string $reason): void
    {
        DB::transaction(function () use ($load, $user, $reason): void {
            $this->assertNoLaterPortfolioPeriods($load);

            $fallback = PortfolioLoad::query()
                ->where('period_key', $load->period_key)
                ->where('status', 'completed')
                ->where('id', '!=', $load->id)
                ->orderByDesc('version')
                ->first();

            if ($load->is_active && ! $fallback) {
                throw new DomainException('Solo se puede anular una carga activa cuando exista una version anterior del mismo periodo para reactivar.');
            }

            $load->forceFill([
                'status' => 'cancelled',
                'is_active' => false,
                'cancelled_at' => now(),
                'cancelled_by' => $user->id,
                'cancellation_reason' => $reason,
            ])->save();

            if ($fallback) {
                $fallback->forceFill(['is_active' => true])->save();
                $this->syncPeriodControlForPortfolio($fallback);
            } else {
                $this->clearPortfolioPeriodControl($load->period_key);
            }
        });
    }

    public function cancelCollectionLoad(CollectionLoad $load, User $user, string $reason): void
    {
        DB::transaction(function () use ($load, $user, $reason): void {
            $this->assertNoLaterCollectionPeriods($load);

            $fallback = CollectionLoad::query()
                ->where('period_key', $load->period_key)
                ->where('status', 'completed')
                ->where('id', '!=', $load->id)
                ->orderByDesc('version')
                ->first();

            if ($load->is_active && ! $fallback) {
                throw new DomainException('Solo se puede anular una carga activa cuando exista una version anterior del mismo periodo para reactivar.');
            }

            $load->forceFill([
                'status' => 'cancelled',
                'is_active' => false,
                'cancelled_at' => now(),
                'cancelled_by' => $user->id,
                'cancellation_reason' => $reason,
            ])->save();

            if ($fallback) {
                $fallback->forceFill(['is_active' => true])->save();
                $this->syncPeriodControlForCollection($fallback);
            } else {
                $this->clearCollectionPeriodControl($load->period_key);
            }
        });
    }

    private function deactivateSamePeriodPortfolioLoads(PortfolioLoad $load): void
    {
        PortfolioLoad::query()
            ->where('period_key', $load->period_key)
            ->where('id', '!=', $load->id)
            ->update(['is_active' => false]);
    }

    private function deactivateSamePeriodCollectionLoads(CollectionLoad $load): void
    {
        CollectionLoad::query()
            ->where('period_key', $load->period_key)
            ->where('id', '!=', $load->id)
            ->update(['is_active' => false]);
    }

    private function syncPeriodControlForPortfolio(PortfolioLoad $load): void
    {
        PeriodControl::query()->updateOrCreate(
            ['period_key' => $load->period_key],
            [
                'period_date' => $load->period_date,
                'portfolio_load_id' => $load->id,
                'portfolio_version' => $load->version,
                'portfolio_loaded_at' => $load->processed_at ?? now(),
            ],
        );
    }

    private function syncPeriodControlForCollection(CollectionLoad $load): void
    {
        PeriodControl::query()->updateOrCreate(
            ['period_key' => $load->period_key],
            [
                'period_date' => $load->period_date,
                'collection_load_id' => $load->id,
                'collection_version' => $load->version,
                'collection_loaded_at' => $load->processed_at ?? now(),
            ],
        );
    }

    private function clearPortfolioPeriodControl(?string $periodKey): void
    {
        if (! $periodKey) {
            return;
        }

        PeriodControl::query()
            ->where('period_key', $periodKey)
            ->update([
                'portfolio_load_id' => null,
                'portfolio_version' => 0,
                'portfolio_loaded_at' => null,
            ]);
    }

    private function clearCollectionPeriodControl(?string $periodKey): void
    {
        if (! $periodKey) {
            return;
        }

        PeriodControl::query()
            ->where('period_key', $periodKey)
            ->update([
                'collection_load_id' => null,
                'collection_version' => 0,
                'collection_loaded_at' => null,
            ]);
    }

    private function assertNoLaterPortfolioPeriods(PortfolioLoad $load): void
    {
        if (! $load->period_date) {
            return;
        }

        $exists = PortfolioLoad::query()
            ->where('status', 'completed')
            ->whereDate('period_date', '>', $load->period_date)
            ->exists();

        if ($exists) {
            throw new DomainException('No se puede anular esta carga porque ya existen periodos posteriores de cartera.');
        }
    }

    private function assertNoLaterCollectionPeriods(CollectionLoad $load): void
    {
        if (! $load->period_date) {
            return;
        }

        $exists = CollectionLoad::query()
            ->where('status', 'completed')
            ->whereDate('period_date', '>', $load->period_date)
            ->exists();

        if ($exists) {
            throw new DomainException('No se puede anular esta carga porque ya existen periodos posteriores de recaudos.');
        }
    }
}
