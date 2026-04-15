<?php

namespace App\Services;

use App\Models\CollectionDetail;
use App\Models\PortfolioDocument;
use App\Models\PortfolioLoad;
use App\Models\PortfolioRotationSnapshot;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PortfolioRotationSnapshotService
{
    public function generateForLoad(PortfolioLoad $load): void
    {
        if (! $load->period_date) {
            return;
        }

        $periodDate = Carbon::parse($load->period_date);

        $agg = PortfolioDocument::query()
            ->where('portfolio_load_id', $load->id)
            ->select(DB::raw('
                COUNT(*) as total_documents,
                SUM(original_amount) as total_portfolio,
                SUM(pending_amount) as total_overdue,
                SUM(CASE WHEN days_overdue > 0 THEN 1 ELSE 0 END) as overdue_documents
            '))
            ->first();

        $totalPortfolio  = (float) ($agg->total_portfolio ?? 0);
        $totalOverdue    = (float) ($agg->total_overdue ?? 0);
        $totalDocuments  = (int) ($agg->total_documents ?? 0);
        $overdueDocuments = (int) ($agg->overdue_documents ?? 0);

        $periodStart = $periodDate->copy()->startOfMonth();
        $periodEnd   = $periodDate->copy()->endOfMonth();

        $collectedPeriod = (float) CollectionDetail::query()
            ->whereBetween('payment_date', [$periodStart->toDateString(), $periodEnd->toDateString()])
            ->sum('amount');

        $dso            = $this->calcDso($totalPortfolio, $collectedPeriod);
        $rotationIndex  = $collectedPeriod > 0 && $totalPortfolio > 0
            ? round($collectedPeriod / $totalPortfolio * 100, 2)
            : 0;
        $overdueRate    = $totalPortfolio > 0
            ? round($totalOverdue / $totalPortfolio * 100, 2)
            : 0;

        $riskDistribution = $this->calcRiskDistribution($load->id);

        PortfolioRotationSnapshot::query()->updateOrCreate(
            ['period_date' => $periodDate->toDateString(), 'client_id' => null],
            [
                'total_portfolio'          => $totalPortfolio,
                'total_overdue'            => $totalOverdue,
                'total_collected_period'   => $collectedPeriod,
                'total_documents'          => $totalDocuments,
                'overdue_documents'        => $overdueDocuments,
                'dso'                      => $dso,
                'rotation_index'           => $rotationIndex,
                'overdue_rate'             => $overdueRate,
                'risk_distribution'        => $riskDistribution,
                'formula_version'          => 1,
            ]
        );
    }

    private function calcDso(float $totalPortfolio, float $collected): float
    {
        if ($collected <= 0) {
            return 0;
        }

        $dailyRevenue = $collected / 30;

        return $dailyRevenue > 0 ? round($totalPortfolio / $dailyRevenue, 1) : 0;
    }

    private function calcRiskDistribution(int $portfolioLoadId): array
    {
        $rows = PortfolioDocument::query()
            ->where('portfolio_load_id', $portfolioLoadId)
            ->select('risk_level', DB::raw('COUNT(*) as cnt, SUM(pending_amount) as amount'))
            ->groupBy('risk_level')
            ->get();

        $dist = [];
        foreach ($rows as $row) {
            $dist[$row->risk_level] = [
                'count'  => (int) $row->cnt,
                'amount' => (float) $row->amount,
            ];
        }

        return $dist;
    }
}
