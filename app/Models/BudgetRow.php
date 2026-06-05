<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BudgetRow extends Model
{
    protected $fillable = [
        'budget_load_id',
        'period_key',
        'row_number',
        'client_name',
        'regional',
        'channel',
        'seller_name',
        'transaction_type',
        'document_number',
        'invoice_date',
        'due_date',
        'days_overdue',
        'initial_amount',
        'balance_due',
        'aging_1_90',
        'aging_over_90',
        'not_due_amount',
        'rotation',
        'budget_amount',
        'collection_amount',
        'category',
        'application_date',
    ];

    protected function casts(): array
    {
        return [
            'invoice_date'      => 'date',
            'due_date'          => 'date',
            'application_date'  => 'date',
            'initial_amount'    => 'decimal:2',
            'balance_due'       => 'decimal:2',
            'aging_1_90'        => 'decimal:2',
            'aging_over_90'     => 'decimal:2',
            'not_due_amount'    => 'decimal:2',
            'rotation'          => 'decimal:4',
            'budget_amount'     => 'decimal:2',
            'collection_amount' => 'decimal:2',
        ];
    }

    public function budgetLoad(): BelongsTo
    {
        return $this->belongsTo(BudgetLoad::class);
    }
}
