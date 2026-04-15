<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortfolioLoadError extends Model
{
    protected $fillable = [
        'portfolio_load_id',
        'row_number',
        'field',
        'error_code',
        'message',
        'row_payload',
    ];

    protected function casts(): array
    {
        return [
            'row_payload' => 'array',
        ];
    }

    public function portfolioLoad(): BelongsTo
    {
        return $this->belongsTo(PortfolioLoad::class, 'portfolio_load_id');
    }
}
