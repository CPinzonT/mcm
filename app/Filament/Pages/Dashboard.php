<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\CollectionTrendWidget;
use App\Filament\Widgets\FollowUpWidget;
use App\Filament\Widgets\PortfolioRotationWidget;
use App\Filament\Widgets\PortfolioStatsWidget;
use App\Filament\Widgets\RiskDistributionWidget;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Widgets\AccountWidget;

class Dashboard extends BaseDashboard
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-home';
    protected static ?string $navigationLabel = 'Dashboard Ejecutivo';
    protected static ?string $title = 'Dashboard Ejecutivo — Cartera Project';
    protected static ?int $navigationSort = 0;
    protected static bool $shouldRegisterNavigation = false;

    public function getWidgets(): array
    {
        return [
            PortfolioStatsWidget::class,
            RiskDistributionWidget::class,
            PortfolioRotationWidget::class,
            CollectionTrendWidget::class,
            FollowUpWidget::class,
        ];
    }

    public function getColumns(): int|array
    {
        return 2;
    }
}
