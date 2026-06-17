<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesRow extends Model
{
    protected $fillable = [
        'sales_load_id',
        'row_number',
        'sale_date',
        'document_number',
        'client_name',
        'client_nit',
        'product_code',
        'product_name',
        'quantity',
        'sale_amount',
        'seller_name',
        'uen',
        'regional',
        'channel',
    ];

    protected function casts(): array
    {
        return [
            'sale_date' => 'date',
            'quantity' => 'decimal:4',
            'sale_amount' => 'decimal:2',
        ];
    }

    public function salesLoad(): BelongsTo
    {
        return $this->belongsTo(SalesLoad::class, 'sales_load_id');
    }
}
