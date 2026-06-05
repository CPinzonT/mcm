<?php

namespace Tests\Unit\Data;

use App\Data\DashboardFiltersData;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class DashboardFiltersDataTest extends TestCase
{
    public function test_consultation_date_uses_date_to_when_set(): void
    {
        $filters = DashboardFiltersData::fromArray([
            'date_to' => '2027-01-02',
        ]);

        $this->assertSame('2027-01-02', $filters->consultationDate());
    }

    public function test_consultation_date_defaults_to_today(): void
    {
        Carbon::setTestNow('2026-05-19');

        $filters = DashboardFiltersData::fromArray([]);

        $this->assertSame('2026-05-19', $filters->consultationDate());

        Carbon::setTestNow();
    }
}
