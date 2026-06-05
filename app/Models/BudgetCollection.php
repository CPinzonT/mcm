<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BudgetCollection extends Model
{
    protected $fillable = [
        'period_date',
        'amount',
        'uen',
        'regional',
        'channel',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'period_date' => 'date',
            'amount'      => 'decimal:2',
        ];
    }
}
