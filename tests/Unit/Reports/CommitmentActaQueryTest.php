<?php

namespace Tests\Unit\Reports;

use App\Services\Reports\CommitmentActaQuery;
use PHPUnit\Framework\TestCase;

class CommitmentActaQueryTest extends TestCase
{
    public function test_resolve_date_range_from_day_range(): void
    {
        $range = CommitmentActaQuery::resolveDateRange('2025-03-10', '2025-03-15', '', '');

        $this->assertSame(['2025-03-10', '2025-03-15'], $range);
    }

    public function test_resolve_date_range_swaps_inverted_days(): void
    {
        $range = CommitmentActaQuery::resolveDateRange('2025-03-20', '2025-03-05', '', '');

        $this->assertSame(['2025-03-05', '2025-03-20'], $range);
    }

    public function test_resolve_date_range_from_month_range(): void
    {
        $range = CommitmentActaQuery::resolveDateRange('', '', '2025-01', '2025-03');

        $this->assertSame(['2025-01-01', '2025-03-31'], $range);
    }

    public function test_day_range_takes_priority_over_months(): void
    {
        $range = CommitmentActaQuery::resolveDateRange('2025-05-01', '2025-05-10', '2024-01', '2024-12');

        $this->assertSame(['2025-05-01', '2025-05-10'], $range);
    }

    public function test_returns_null_when_no_range(): void
    {
        $this->assertNull(CommitmentActaQuery::resolveDateRange('', '', '', ''));
    }
}
