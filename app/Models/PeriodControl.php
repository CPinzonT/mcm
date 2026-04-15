<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PeriodControl extends Model
{
    protected $fillable = [
        'period_key',
        'period_date',
        'portfolio_load_id',
        'portfolio_version',
        'portfolio_loaded_at',
        'collection_load_id',
        'collection_version',
        'collection_loaded_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'period_date' => 'date',
            'portfolio_loaded_at' => 'datetime',
            'collection_loaded_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function portfolioLoad(): BelongsTo
    {
        return $this->belongsTo(PortfolioLoad::class);
    }

    public function collectionLoad(): BelongsTo
    {
        return $this->belongsTo(CollectionLoad::class);
    }
}
