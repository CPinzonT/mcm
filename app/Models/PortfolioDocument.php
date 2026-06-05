<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class PortfolioDocument extends Model implements HasMedia
{
    use SoftDeletes, InteractsWithMedia;

    /** Estados con saldo en gestión (mora, vencido, gestión). */
    public const OPERATIVE_STATUSES = ['active', 'partial', 'in_process'];

    /**
     * Estados que componen el total de cartera de la carga (importados + pagados con crédito).
     * Excluye solo "closed" (documentos arrastrados por comparación de cortes).
     */
    public const BALANCE_STATUSES = ['active', 'partial', 'in_process', 'paid'];

    protected $fillable = [
        'client_id', 'portfolio_load_id', 'advisor_id', 'account', 'logical_key',
        'document_number', 'client_reference', 'document_type', 'issue_date', 'activation_date', 'due_date',
        'original_amount', 'pending_amount', 'collected_amount',
        'days_overdue', 'aging_buckets', 'risk_level', 'status', 'currency',
        'period_date', 'notes', 'closed_at', 'closure_reason',
    ];

    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'activation_date' => 'date',
            'due_date' => 'date',
            'period_date' => 'date',
            'closed_at' => 'datetime',
            'aging_buckets' => 'array',
            'original_amount' => 'decimal:2',
            'pending_amount' => 'decimal:2',
            'collected_amount' => 'decimal:2',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function portfolioLoad(): BelongsTo
    {
        return $this->belongsTo(PortfolioLoad::class);
    }

    public function advisor(): BelongsTo
    {
        return $this->belongsTo(Advisor::class);
    }

    public function managementLogs(): HasMany
    {
        return $this->hasMany(ManagementLog::class);
    }

    public function collectionDetails(): HasMany
    {
        return $this->hasMany(CollectionDetail::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', self::OPERATIVE_STATUSES);
    }

    public function scopeByRisk(Builder $query, string $risk): Builder
    {
        return $query->where('risk_level', $risk);
    }

    public function scopeByAdvisor(Builder $query, int $advisorId): Builder
    {
        return $query->where('advisor_id', $advisorId);
    }

    public function scopeByPeriod(Builder $query, string $period): Builder
    {
        return $query->where('period_date', $period);
    }

    public function getRiskLabelAttribute(): string
    {
        return match($this->risk_level) {
            'normal'   => 'Normal',
            'low'      => 'Riesgo Bajo',
            'medium'   => 'Riesgo Medio',
            'high'     => 'Riesgo Alto',
            'critical' => 'Crítico',
            default    => 'Desconocido',
        };
    }
}
