<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientHistory extends Model
{
    protected $fillable = [
        'client_id', 'event_date', 'event_type', 'amount', 'description',
        'portfolio_document_id', 'portfolio_load_id', 'collection_detail_id',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'datetime',
            'amount' => 'decimal:2',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function portfolioDocument(): BelongsTo
    {
        return $this->belongsTo(PortfolioDocument::class);
    }

    public function portfolioLoad(): BelongsTo
    {
        return $this->belongsTo(PortfolioLoad::class);
    }

    public function collectionDetail(): BelongsTo
    {
        return $this->belongsTo(CollectionDetail::class);
    }
}
