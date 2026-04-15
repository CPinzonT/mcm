<?php

namespace App\Filament\Widgets;

use App\Models\CollectionDetail;
use Filament\Widgets\ChartWidget;

class CollectionTrendWidget extends ChartWidget
{
    protected ?string $heading = 'Tendencia de Recaudos (Últimos 6 meses)';
    protected static ?int $sort = 4;

    protected function getData(): array
    {
        $months = collect(range(5, 0))->map(function ($offset) {
            $date = now()->subMonths($offset);
            return [
                'label'  => $date->format('M Y'),
                'month'  => $date->month,
                'year'   => $date->year,
            ];
        });

        $collected = $months->map(fn ($m) =>
            (float) CollectionDetail::whereMonth('payment_date', $m['month'])
                ->whereYear('payment_date', $m['year'])
                ->sum('amount')
        );

        return [
            'datasets' => [[
                'label'           => 'Recaudado',
                'data'            => $collected->values()->toArray(),
                'backgroundColor' => 'rgba(16,185,129,0.7)',
                'borderColor'     => '#10b981',
                'borderWidth'     => 2,
            ]],
            'labels' => $months->pluck('label')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => ['legend' => ['display' => false]],
            'scales'  => ['y' => ['beginAtZero' => true]],
        ];
    }
}
