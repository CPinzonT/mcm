<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientContactLoad extends Model
{
    protected $fillable = [
        'reference',
        'original_filename',
        'disk',
        'path',
        'total_rows',
        'updated_rows',
        'not_found_rows',
        'skipped_rows',
        'error_rows',
        'status',
        'error_log',
        'uploaded_by',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'error_log' => 'array',
            'processed_at' => 'datetime',
        ];
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
