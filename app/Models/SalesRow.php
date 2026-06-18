<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesRow extends Model
{
    protected $fillable = [
        'sales_load_id',
        'client_id',
        'row_number',
        'sale_date',
        'document_number',
        'invoice_type',
        'client_name',
        'client_nit',
        'client_code',
        'product_code',
        'product_name',
        'quantity',
        'sale_amount',
        'cost_amount',
        'gross_profit',
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
            'cost_amount' => 'decimal:2',
            'gross_profit' => 'decimal:2',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function salesLoad(): BelongsTo
    {
        return $this->belongsTo(SalesLoad::class, 'sales_load_id');
    }
}
