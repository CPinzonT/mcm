<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CollectionReconciliation extends Model
{
    protected $fillable = [
        'collection_detail_id', 'portfolio_document_id', 'collection_load_id',
        'document_number', 'client_portfolio', 'client_collection',
        'invoice_amount', 'applied_amount', 'portfolio_pending', 'difference', 'resulting_balance',
        'status', 'period_portfolio', 'period_collection',
        'confidence_level', 'validation_detail', 'notes', 'reconciled_at',
    ];

    protected function casts(): array
    {
        return [
            'invoice_amount' => 'decimal:2',
            'applied_amount' => 'decimal:2',
            'portfolio_pending' => 'decimal:2',
            'difference' => 'decimal:2',
            'resulting_balance' => 'decimal:2',
            'reconciled_at' => 'datetime',
        ];
    }

    public function collectionDetail(): BelongsTo
    {
        return $this->belongsTo(CollectionDetail::class);
    }

    public function portfolioDocument(): BelongsTo
    {
        return $this->belongsTo(PortfolioDocument::class);
    }

    public function collectionLoad(): BelongsTo
    {
        return $this->belongsTo(CollectionLoad::class);
    }
}
