<?php

namespace Tests\Unit\Risk;

use App\Services\Risk\RiskClassificationService;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\TestCase;

class RiskClassificationServiceTest extends TestCase
{
    public function test_days_overdue_is_zero_on_due_date(): void
    {
        $service = new RiskClassificationService();
        $due = CarbonImmutable::parse('2026-01-01');

        $this->assertSame(0, $service->daysOverdueAfterDue($due, $due));
    }

    public function test_days_overdue_is_one_day_after_due_date(): void
    {
        $service = new RiskClassificationService();
        $due = CarbonImmutable::parse('2026-01-01');
        $cut = CarbonImmutable::parse('2026-01-02');

        $this->assertSame(1, $service->daysOverdueAfterDue($due, $cut));
    }

    public function test_days_overdue_is_zero_before_due_date(): void
    {
        $service = new RiskClassificationService();
        $due = CarbonImmutable::parse('2026-01-10');
        $cut = CarbonImmutable::parse('2026-01-02');

        $this->assertSame(0, $service->daysOverdueAfterDue($due, $cut));
    }
}
