<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CollectionLoadError extends Model
{
    protected $fillable = [
        'collection_load_id',
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

    public function collectionLoad(): BelongsTo
    {
        return $this->belongsTo(CollectionLoad::class, 'collection_load_id');
    }
}
