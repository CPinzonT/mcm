<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ManagementLog extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'client_id', 'portfolio_document_id', 'advisor_id', 'user_id',
        'type', 'subject', 'description', 'contact_date',
        'result', 'follow_up_date', 'promised_amount', 'promised_date', 'status',
    ];

    protected function casts(): array
    {
        return [
            'contact_date'    => 'date',
            'follow_up_date'  => 'date',
            'promised_date'   => 'date',
            'promised_amount' => 'decimal:2',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function portfolioDocument(): BelongsTo
    {
        return $this->belongsTo(PortfolioDocument::class);
    }

    public function advisor(): BelongsTo
    {
        return $this->belongsTo(Advisor::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getTypeLabel(): string
    {
        return match($this->type) {
            'call'      => 'Llamada',
            'email'     => 'Correo',
            'visit'     => 'Visita',
            'agreement' => 'Acuerdo',
            'legal'     => 'Jurídico',
            default     => 'Otro',
        };
    }
}
