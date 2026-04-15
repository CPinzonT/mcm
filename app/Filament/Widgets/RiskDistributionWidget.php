<?php

namespace App\Filament\Widgets;

use App\Models\PortfolioDocument;
use Filament\Widgets\ChartWidget;

class RiskDistributionWidget extends ChartWidget
{
    protected ?string $heading = 'Distribución por Riesgo de Mora';
    protected static ?int $sort = 2;


    protected function getData(): array
    {
        $distribution = PortfolioDocument::whereIn('status', ['active', 'partial', 'in_process'])
            ->selectRaw('risk_level, SUM(pending_amount) as total, COUNT(*) as count')
            ->groupBy('risk_level')
            ->get()
            ->keyBy('risk_level');

        $labels  = ['Normal (0-30d)', 'Bajo (31-60d)', 'Medio (61-90d)', 'Alto (91-180d)', 'Crítico (181+d)'];
        $keys    = ['normal', 'low', 'medium', 'high', 'critical'];
        $amounts = array_map(fn ($k) => (float) ($distribution[$k]->total ?? 0), $keys);
        $colors  = ['#10b981', '#3b82f6', '#f59e0b', '#f97316', '#ef4444'];

        return [
            'datasets' => [[
                'label'           => 'Saldo Pendiente',
                'data'            => $amounts,
                'backgroundColor' => $colors,
                'borderWidth'     => 2,
                'borderColor'     => '#ffffff',
            ]],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['position' => 'bottom'],
            ],
        ];
    }
}
