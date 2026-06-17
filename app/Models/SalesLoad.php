<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class SalesLoad extends Model
{
    protected $fillable = [
        'reference',
        'original_filename',
        'source_url',
        'disk',
        'path',
        'file_hash',
        'period_key',
        'total_rows',
        'valid_rows',
        'processed_rows',
        'error_rows',
        'total_amount',
        'status',
        'notes',
        'validation_summary',
        'error_log',
        'uploaded_by',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'validation_summary' => 'array',
            'error_log' => 'array',
            'total_amount' => 'decimal:2',
            'processed_at' => 'datetime',
        ];
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->uploader();
    }

    public function rows(): HasMany
    {
        return $this->hasMany(SalesRow::class);
    }

    public function audits(): MorphMany
    {
        return $this->morphMany(LoadAudit::class, 'auditable');
    }
}
