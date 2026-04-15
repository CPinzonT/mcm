<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CollectionDetail extends Model
{
    protected $fillable = [
        'collection_load_id', 'row_number', 'client_id', 'client_name', 'portfolio_document_id',
        'document_number', 'document_type', 'receipt_number', 'reconciliation_id',
        'applied_document_type', 'amount', 'applied_amount',
        'pending_amount_after', 'payment_date', 'period_key', 'period_date',
        'payment_method', 'bank', 'reference', 'notes',
        'regional', 'channel', 'uen', 'seller_name', 'source_payload',
        'bucket', 'reconciliation_status', 'reconciliation_notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'applied_amount' => 'decimal:2',
            'pending_amount_after' => 'decimal:2',
            'payment_date' => 'date',
            'period_date' => 'date',
            'source_payload' => 'array',
        ];
    }

    public function collectionLoad(): BelongsTo
    {
        return $this->belongsTo(CollectionLoad::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function portfolioDocument(): BelongsTo
    {
        return $this->belongsTo(PortfolioDocument::class);
    }
}
