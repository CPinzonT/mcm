<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReportTemplate extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'slug', 'description', 'type',
        'client_id', 'branding_profile_id',
        'visible_columns', 'default_filters',
        'title', 'subtitle',
        'show_logo', 'show_header', 'show_footer', 'show_page_numbers',
        'active', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'visible_columns'  => 'array',
            'default_filters'  => 'array',
            'show_logo'        => 'boolean',
            'show_header'      => 'boolean',
            'show_footer'      => 'boolean',
            'show_page_numbers'=> 'boolean',
            'active'           => 'boolean',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function brandingProfile(): BelongsTo
    {
        return $this->belongsTo(BrandingProfile::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function columns(): HasMany
    {
        return $this->hasMany(ReportTemplateColumn::class)->orderBy('order');
    }
}
