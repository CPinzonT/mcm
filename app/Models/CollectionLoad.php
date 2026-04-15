<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class CollectionLoad extends Model
{
    protected $fillable = [
        'reference', 'original_filename', 'disk', 'path', 'file_hash',
        'total_rows', 'valid_rows', 'processed_rows', 'error_rows', 'empty_rows', 'duplicate_rows',
        'detail_count', 'total_collected',
        'status', 'notes', 'error_log', 'validation_summary',
        'processed_at', 'uploaded_by', 'cancelled_by', 'cancelled_at', 'cancellation_reason',
        'period_date', 'period_key', 'version', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'error_log' => 'array',
            'validation_summary' => 'array',
            'processed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'period_date' => 'date',
            'is_active' => 'boolean',
            'total_collected' => 'decimal:2',
        ];
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function details(): HasMany
    {
        return $this->hasMany(CollectionDetail::class);
    }

    public function errors(): HasMany
    {
        return $this->hasMany(CollectionLoadError::class);
    }

    public function audits(): MorphMany
    {
        return $this->morphMany(LoadAudit::class, 'auditable');
    }
}
