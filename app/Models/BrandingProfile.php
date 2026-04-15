<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BrandingProfile extends Model
{
    protected $fillable = [
        'name', 'is_default', 'company_name', 'logo_path',
        'primary_color', 'secondary_color', 'accent_color',
        'font_family', 'header_text', 'footer_text',
        'address', 'phone', 'email', 'website',
    ];

    protected function casts(): array
    {
        return ['is_default' => 'boolean'];
    }

    public function reportTemplates(): HasMany
    {
        return $this->hasMany(ReportTemplate::class);
    }

    public static function getDefault(): ?self
    {
        return static::where('is_default', true)->first();
    }
}
