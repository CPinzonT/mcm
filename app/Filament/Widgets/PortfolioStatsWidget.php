<?php

namespace App\Filament\Widgets;

use App\Models\Client;
use App\Models\PortfolioDocument;
use App\Models\CollectionDetail;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PortfolioStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $totalPortfolio = PortfolioDocument::whereIn('status', ['active', 'partial', 'in_process'])
            ->sum('pending_amount');

        $totalOverdue = PortfolioDocument::whereIn('status', ['active', 'partial', 'in_process'])
            ->where('days_overdue', '>', 0)
            ->sum('pending_amount');

        $overdueRate = $totalPortfolio > 0
            ? round(($totalOverdue / $totalPortfolio) * 100, 1)
            : 0;

        $collectedThisMonth = CollectionDetail::whereMonth('payment_date', now()->month)
            ->whereYear('payment_date', now()->year)
            ->sum('amount');

        $activeClients = Client::active()->count();

        $criticalDocs = PortfolioDocument::where('risk_level', 'critical')
            ->whereIn('status', ['active', 'partial', 'in_process'])
            ->count();

        return [
            Stat::make('Cartera Activa Total', '$' . number_format($totalPortfolio, 0, ',', '.'))
                ->description('Saldo total pendiente')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('primary'),

            Stat::make('Cartera Vencida', '$' . number_format($totalOverdue, 0, ',', '.'))
                ->description("{$overdueRate}% del total")
                ->descriptionIcon('heroicon-m-exclamation-circle')
                ->color($overdueRate > 15 ? 'danger' : ($overdueRate > 8 ? 'warning' : 'success')),

            Stat::make('Recaudado (Mes Actual)', '$' . number_format($collectedThisMonth, 0, ',', '.'))
                ->description('Recaudos del mes')
                ->descriptionIcon('heroicon-m-arrow-down-circle')
                ->color('success'),

            Stat::make('Clientes Activos', $activeClients)
                ->description('Clientes en cartera')
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color('info'),

            Stat::make('Documentos Críticos', $criticalDocs)
                ->description('Mora > 180 días')
                ->descriptionIcon('heroicon-m-fire')
                ->color($criticalDocs > 0 ? 'danger' : 'success'),
        ];
    }
}
