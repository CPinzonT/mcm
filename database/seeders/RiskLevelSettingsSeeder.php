<?php

namespace Database\Seeders;

use App\Models\RiskLevelSetting;
use Illuminate\Database\Seeder;

class RiskLevelSettingsSeeder extends Seeder
{
    public function run(): void
    {
        // Días contados desde el día 1 después del vencimiento (no desde emisión).
        $levels = [
            ['level' => 'normal',   'label' => 'Corriente',     'days_min' => 0,   'days_max' => 0,   'color' => '#10b981', 'badge_color' => 'success', 'order' => 1],
            ['level' => 'low',      'label' => 'Riesgo Bajo',   'days_min' => 1,   'days_max' => 30,  'color' => '#3b82f6', 'badge_color' => 'info',    'order' => 2],
            ['level' => 'medium',   'label' => 'Riesgo Medio',  'days_min' => 31,  'days_max' => 60,  'color' => '#f59e0b', 'badge_color' => 'warning', 'order' => 3],
            ['level' => 'high',     'label' => 'Riesgo Alto',   'days_min' => 61,  'days_max' => 90,  'color' => '#f97316', 'badge_color' => 'warning', 'order' => 4],
            ['level' => 'critical', 'label' => 'Crítico',       'days_min' => 91,  'days_max' => null, 'color' => '#ef4444', 'badge_color' => 'danger',  'order' => 5],
        ];

        foreach ($levels as $level) {
            RiskLevelSetting::updateOrCreate(['level' => $level['level']], $level);
        }
    }
}
