<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortfolioRotationSnapshot extends Model
{
    protected $fillable = [
        'period_date', 'client_id',
        'total_portfolio', 'total_overdue', 'total_collected_period',
        'total_documents', 'overdue_documents',
        'dso', 'rotation_index', 'overdue_rate',
        'risk_distribution', 'formula_version',
    ];

    protected function casts(): array
    {
        return [
            'period_date'            => 'date',
            'total_portfolio'        => 'decimal:2',
            'total_overdue'          => 'decimal:2',
            'total_collected_period' => 'decimal:2',
            'dso'                    => 'decimal:2',
            'rotation_index'         => 'decimal:4',
            'overdue_rate'           => 'decimal:4',
            'risk_distribution'      => 'array',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
