<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code', 'name', 'document_type', 'document_number',
        'email', 'phone', 'address', 'city', 'region',
        'channel', 'uen', 'credit_limit', 'payment_term_days',
        'contact_name', 'contact_email', 'contact_phone', 'active', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'credit_limit' => 'decimal:2',
            'payment_term_days' => 'integer',
        ];
    }

    public function portfolioDocuments(): HasMany
    {
        return $this->hasMany(PortfolioDocument::class);
    }

    public function collectionDetails(): HasMany
    {
        return $this->hasMany(CollectionDetail::class);
    }

    public function managementLogs(): HasMany
    {
        return $this->hasMany(ManagementLog::class);
    }

    public function castigoCases(): HasMany
    {
        return $this->hasMany(CastigoCase::class);
    }

    public function rotationSnapshots(): HasMany
    {
        return $this->hasMany(PortfolioRotationSnapshot::class);
    }

    public function reportTemplates(): HasMany
    {
        return $this->hasMany(ReportTemplate::class);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}
