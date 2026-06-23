<?php

namespace Tests\Unit\Models;

use App\Models\CollectionDetail;
use App\Models\PortfolioDocument;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class CollectionDetailPaymentTimingTest extends TestCase
{
    public function test_early_payment_when_payment_date_is_before_due_date(): void
    {
        $detail = $this->makeDetail('2026-05-01', '2026-05-15');

        $this->assertSame(14, $detail->paymentDaysBeforeDue());
        $this->assertTrue($detail->isEarlyPayment());
    }

    public function test_not_early_when_payment_is_on_due_date(): void
    {
        $detail = $this->makeDetail('2026-05-15', '2026-05-15');

        $this->assertSame(0, $detail->paymentDaysBeforeDue());
        $this->assertFalse($detail->isEarlyPayment());
    }

    public function test_negative_days_when_payment_is_after_due_date(): void
    {
        $detail = $this->makeDetail('2026-05-20', '2026-05-15');

        $this->assertSame(-5, $detail->paymentDaysBeforeDue());
        $this->assertFalse($detail->isEarlyPayment());
    }

    public function test_null_when_invoice_due_date_is_missing(): void
    {
        $detail = new CollectionDetail([
            'payment_date' => Carbon::parse('2026-05-01'),
        ]);

        $this->assertNull($detail->paymentDaysBeforeDue());
        $this->assertFalse($detail->isEarlyPayment());
    }

    private function makeDetail(string $paymentDate, string $dueDate): CollectionDetail
    {
        $document = new PortfolioDocument([
            'due_date' => Carbon::parse($dueDate),
        ]);

        $detail = new CollectionDetail([
            'payment_date' => Carbon::parse($paymentDate),
        ]);
        $detail->setRelation('portfolioDocument', $document);

        return $detail;
    }
}
