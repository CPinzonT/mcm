<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

trait InteractsWithPaymentTiming
{
    public static function latestActiveCollectionLoadId(): ?int
    {
        static $resolved = false;
        static $loadId = null;

        if ($resolved) {
            return $loadId;
        }

        $resolved = true;

        $id = DB::table('collection_loads')
            ->where('is_active', true)
            ->where('status', 'completed')
            ->orderByDesc('processed_at')
            ->orderByDesc('version')
            ->orderByDesc('id')
            ->value('id');

        $loadId = $id !== null ? (int) $id : null;

        return $loadId;
    }

    public function paymentDaysBeforeDue(): ?int
    {
        $dueDate = $this->portfolioDocument?->due_date;
        $paymentDate = $this->payment_date;

        if ($dueDate === null || $paymentDate === null) {
            return null;
        }

        $payment = $paymentDate->startOfDay();
        $due = $dueDate->startOfDay();

        if ($payment->lt($due)) {
            return (int) $payment->diffInDays($due);
        }

        if ($payment->eq($due)) {
            return 0;
        }

        return -1 * (int) $due->diffInDays($payment);
    }

    public function isEarlyPayment(): bool
    {
        $days = $this->paymentDaysBeforeDue();

        return $days !== null && $days > 0;
    }

    public function scopeForLatestActiveLoad(Builder $query): Builder
    {
        $loadId = static::latestActiveCollectionLoadId();

        if ($loadId === null) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where($query->getModel()->getTable() . '.collection_load_id', $loadId);
    }

    public function scopeEarlyPayment(Builder $query): Builder
    {
        $table = $query->getModel()->getTable();

        return $query
            ->whereNotNull("{$table}.payment_date")
            ->whereExists(function (Builder $sub) use ($table): void {
                $sub->selectRaw('1')
                    ->from('portfolio_documents as pd')
                    ->whereColumn('pd.id', "{$table}.portfolio_document_id")
                    ->whereNotNull('pd.due_date')
                    ->whereColumn("{$table}.payment_date", '<', 'pd.due_date');
            });
    }
}
