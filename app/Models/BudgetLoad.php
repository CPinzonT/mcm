<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BudgetLoad extends Model
{
    protected $fillable = [
        'reference',
        'original_filename',
        'disk',
        'path',
        'period_key',
        'total_rows',
        'valid_rows',
        'error_rows',
        'total_amount',
        'status',
        'notes',
        'error_log',
        'uploaded_by',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'error_log'    => 'array',
            'processed_at' => 'datetime',
        ];
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function rows(): HasMany
    {
        return $this->hasMany(BudgetRow::class);
    }
}
