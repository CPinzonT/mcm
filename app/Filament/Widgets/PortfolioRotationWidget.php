<?php

namespace App\Filament\Widgets;

use App\Models\PortfolioRotationSnapshot;
use Filament\Widgets\ChartWidget;

class PortfolioRotationWidget extends ChartWidget
{
    protected ?string $heading = 'Evolución de Rotación de Cartera (DSO)';
    protected static ?int $sort = 3;
    protected ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $snapshots = PortfolioRotationSnapshot::whereNull('client_id')
            ->orderBy('period_date')
            ->latest('period_date')
            ->take(12)
            ->get()
            ->sortBy('period_date');

        $labels = $snapshots->map(fn ($s) => $s->period_date->format('M Y'))->values()->toArray();
        $dso    = $snapshots->map(fn ($s) => (float) $s->dso)->values()->toArray();
        $rates  = $snapshots->map(fn ($s) => round((float) $s->overdue_rate * 100, 2))->values()->toArray();

        return [
            'datasets' => [
                [
                    'label'           => 'DSO (días)',
                    'data'            => $dso,
                    'borderColor'     => '#2563eb',
                    'backgroundColor' => 'rgba(37,99,235,0.1)',
                    'tension'         => 0.4,
                    'fill'            => true,
                    'yAxisID'         => 'y',
                ],
                [
                    'label'           => 'Tasa de Mora (%)',
                    'data'            => $rates,
                    'borderColor'     => '#ef4444',
                    'backgroundColor' => 'rgba(239,68,68,0.05)',
                    'tension'         => 0.4,
                    'fill'            => false,
                    'yAxisID'         => 'y1',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['position' => 'top'],
                'tooltip' => ['mode' => 'index', 'intersect' => false],
            ],
            'scales' => [
                'y'  => ['position' => 'left', 'title' => ['display' => true, 'text' => 'DSO (días)']],
                'y1' => ['position' => 'right', 'title' => ['display' => true, 'text' => 'Mora (%)'],
                    'grid' => ['drawOnChartArea' => false]],
            ],
        ];
    }
}
