<?php

namespace App\Services\Risk;

use App\Models\RiskLevelSetting;
use Carbon\CarbonImmutable;

/**
 * Clasificación de mora y riesgo a partir de la fecha de vencimiento.
 *
 * Regla: días de mora = días calendario entre vencimiento y la fecha de consulta.
 * Si vence el 01/01/2026 y se consulta el 02/01/2026 → 1 día de mora.
 * En la fecha de vencimiento (o antes) → 0 días.
 */
class RiskClassificationService
{
    /** Fecha de referencia al cargar (siempre el día de la carga / hoy). */
    public function cutDateForPeriod(CarbonImmutable $periodDate): CarbonImmutable
    {
        return $this->referenceDate();
    }

    public function referenceDate(?CarbonImmutable $asOf = null): CarbonImmutable
    {
        return ($asOf ?? CarbonImmutable::today())->startOfDay();
    }

    public function daysOverdueAsOf(CarbonImmutable $dueDate, ?CarbonImmutable $asOf = null): int
    {
        return $this->daysOverdueAfterDue($dueDate, $this->referenceDate($asOf));
    }

    public function daysOverdueAfterDue(CarbonImmutable $dueDate, CarbonImmutable $cutDate): int
    {
        $due = $dueDate->startOfDay();
        $cut = $cutDate->startOfDay();

        if ($cut->lte($due)) {
            return 0;
        }

        return (int) $due->diffInDays($cut, false);
    }

    public function riskLevelForDays(int $daysOverdue): string
    {
        if ($daysOverdue <= 0) {
            return 'normal';
        }

        return RiskLevelSetting::resolveRisk($daysOverdue);
    }

    /**
     * @return array<string, float>
     */
    public function agingBucketsFor(float $pendingAmount, int $daysOverdue): array
    {
        $buckets = [
            'actual' => 0.0,
            '1_30_dias' => 0.0,
            '31_60_dias' => 0.0,
            '61_90_dias' => 0.0,
            '91_180_dias' => 0.0,
            '181_360_dias' => 0.0,
            '361_dias' => 0.0,
        ];

        $target = match (true) {
            $pendingAmount <= 0 => 'actual',
            $daysOverdue <= 0 => 'actual',
            $daysOverdue <= 30 => '1_30_dias',
            $daysOverdue <= 60 => '31_60_dias',
            $daysOverdue <= 90 => '61_90_dias',
            $daysOverdue <= 180 => '91_180_dias',
            $daysOverdue <= 360 => '181_360_dias',
            default => '361_dias',
        };

        $buckets[$target] = round($pendingAmount, 2);

        return $buckets;
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    public function applyToRow(array $row, CarbonImmutable $periodDate): array
    {
        $cutDate = $this->cutDateForPeriod($periodDate);
        $dueDate = CarbonImmutable::parse($row['due_date']);
        $daysOverdue = $this->daysOverdueAfterDue($dueDate, $cutDate);
        $pending = (float) ($row['pending_amount'] ?? 0);

        $row['days_overdue'] = $daysOverdue;
        $row['aging_buckets'] = $this->agingBucketsFor($pending, $daysOverdue);
        $row['risk_level'] = $this->riskLevelForDays($daysOverdue);

        return $row;
    }
}
