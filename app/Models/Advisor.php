<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Advisor extends Model
{
    use SoftDeletes;

    protected $fillable = ['code', 'name', 'email', 'phone', 'region', 'active', 'user_id'];

    protected function casts(): array
    {
        return ['active' => 'boolean'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function portfolioDocuments(): HasMany
    {
        return $this->hasMany(PortfolioDocument::class);
    }

    public function managementLogs(): HasMany
    {
        return $this->hasMany(ManagementLog::class);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}
