<?php

namespace Tests\Unit\Clients;

use App\Services\Clients\ClientSalesRotationService;
use PHPUnit\Framework\TestCase;

class ClientSalesRotationServiceTest extends TestCase
{
    public function test_rotation_days_uses_portfolio_over_sales_times_360(): void
    {
        $service = new ClientSalesRotationService();

        $this->assertSame(72.0, $service->rotationDaysFromSales(20_000_000, 100_000_000));
    }

    public function test_rotation_is_null_without_positive_portfolio_or_sales(): void
    {
        $service = new ClientSalesRotationService();

        $this->assertNull($service->rotationDaysFromSales(0, 100_000_000));
        $this->assertNull($service->rotationDaysFromSales(20_000_000, 0));
    }
}
