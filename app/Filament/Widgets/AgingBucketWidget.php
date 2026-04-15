<?php

namespace App\Filament\Widgets;

use App\Models\CollectionDetail;
use App\Models\PortfolioDocument;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class AgingBucketWidget extends ChartWidget
{
    protected ?string $heading = 'Distribución Cartera por Bucket de Mora';

    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $buckets = [
            'Corriente'   => [0, 0],
            '1-30 días'   => [1, 30],
            '31-60 días'  => [31, 60],
            '61-90 días'  => [61, 90],
            '91-120 días' => [91, 120],
            '121-180 días'=> [121, 180],
            '181-360 días'=> [181, 360],
            '+360 días'   => [361, PHP_INT_MAX],
        ];

        $data = [];
        $labels = [];

        $query = PortfolioDocument::query()
            ->whereIn('status', ['active', 'partial', 'in_process'])
            ->whereHas('portfolioLoad', fn ($q) => $q->where('is_active', true)->where('status', 'completed'))
            ->select(DB::raw('
                SUM(CASE WHEN days_overdue <= 0 THEN pending_amount ELSE 0 END) as corriente,
                SUM(CASE WHEN days_overdue BETWEEN 1 AND 30 THEN pending_amount ELSE 0 END) as b1_30,
                SUM(CASE WHEN days_overdue BETWEEN 31 AND 60 THEN pending_amount ELSE 0 END) as b31_60,
                SUM(CASE WHEN days_overdue BETWEEN 61 AND 90 THEN pending_amount ELSE 0 END) as b61_90,
                SUM(CASE WHEN days_overdue BETWEEN 91 AND 120 THEN pending_amount ELSE 0 END) as b91_120,
                SUM(CASE WHEN days_overdue BETWEEN 121 AND 180 THEN pending_amount ELSE 0 END) as b121_180,
                SUM(CASE WHEN days_overdue BETWEEN 181 AND 360 THEN pending_amount ELSE 0 END) as b181_360,
                SUM(CASE WHEN days_overdue > 360 THEN pending_amount ELSE 0 END) as b360p
            '))
            ->first();

        if (! $query) {
            return ['datasets' => [['data' => []]], 'labels' => []];
        }

        $values = [
            (float) $query->corriente,
            (float) $query->b1_30,
            (float) $query->b31_60,
            (float) $query->b61_90,
            (float) $query->b91_120,
            (float) $query->b121_180,
            (float) $query->b181_360,
            (float) $query->b360p,
        ];

        return [
            'datasets' => [[
                'data' => $values,
                'backgroundColor' => [
                    'rgba(16,185,129,0.8)',
                    'rgba(251,191,36,0.8)',
                    'rgba(245,158,11,0.8)',
                    'rgba(249,115,22,0.8)',
                    'rgba(239,68,68,0.8)',
                    'rgba(220,38,38,0.8)',
                    'rgba(185,28,28,0.8)',
                    'rgba(127,29,29,0.8)',
                ],
            ]],
            'labels' => array_keys($buckets),
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
                'legend' => ['position' => 'right'],
                'tooltip' => [
                    'callbacks' => [],
                ],
            ],
            'cutout' => '60%',
        ];
    }
}
