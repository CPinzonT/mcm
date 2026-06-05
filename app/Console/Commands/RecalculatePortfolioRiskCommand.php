<?php

namespace App\Console\Commands;

use App\Models\PortfolioDocument;
use App\Models\PortfolioLoad;
use App\Services\Risk\RiskClassificationService;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class RecalculatePortfolioRiskCommand extends Command
{
    protected $signature = 'portfolio:recalculate-risk
                            {--load= : ID de carga de cartera activa}
                            {--period= : Periodo YYYY-MM}';

    protected $description = 'Recalcula dias de mora y nivel de riesgo desde la fecha de vencimiento (corte de periodo).';

    public function handle(RiskClassificationService $risk): int
    {
        $loadId = $this->option('load');
        $periodKey = $this->option('period');

        $loadQuery = PortfolioLoad::query()
            ->where('status', 'completed')
            ->where('is_active', true);

        if ($loadId) {
            $loadQuery->whereKey($loadId);
        } elseif ($periodKey) {
            $loadQuery->where('period_key', $periodKey);
        }

        $load = $loadQuery->orderByDesc('id')->first();

        if (! $load?->period_date) {
            $this->error('No se encontro una carga activa con periodo definido.');

            return self::FAILURE;
        }

        $periodDate = CarbonImmutable::parse($load->period_date);
        $updated = 0;

        PortfolioDocument::query()
            ->where('portfolio_load_id', $load->id)
            ->whereNotNull('due_date')
            ->chunkById(500, function ($documents) use ($risk, $periodDate, &$updated): void {
                foreach ($documents as $document) {
                    $dueDate = CarbonImmutable::parse($document->due_date);
                    $daysOverdue = $risk->daysOverdueAfterDue($dueDate, $risk->cutDateForPeriod($periodDate));

                    $document->update([
                        'days_overdue' => $daysOverdue,
                        'aging_buckets' => $risk->agingBucketsFor((float) $document->pending_amount, $daysOverdue),
                        'risk_level' => $risk->riskLevelForDays($daysOverdue),
                    ]);

                    $updated++;
                }
            });

        $this->info("Carga {$load->reference}: {$updated} documentos actualizados.");

        return self::SUCCESS;
    }
}
