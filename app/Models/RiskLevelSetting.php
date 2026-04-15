<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiskLevelSetting extends Model
{
    protected $fillable = [
        'level', 'label', 'days_min', 'days_max',
        'color', 'badge_color', 'order',
    ];

    public static function resolveRisk(int $daysOverdue): string
    {
        $setting = static::where('days_min', '<=', $daysOverdue)
            ->where(function ($q) use ($daysOverdue) {
                $q->whereNull('days_max')->orWhere('days_max', '>=', $daysOverdue);
            })
            ->orderBy('days_min', 'desc')
            ->first();

        return $setting?->level ?? 'normal';
    }
}
