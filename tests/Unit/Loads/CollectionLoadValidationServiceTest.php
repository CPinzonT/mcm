<?php

namespace Tests\Unit\Loads;

use App\Services\Loads\CollectionLoadValidationService;
use App\Services\Loads\PeriodControlService;
use App\Services\Loads\Support\ImportNormalizer;
use App\Services\Loads\Support\SpreadsheetReader;
use Mockery;
use PHPUnit\Framework\TestCase;

class CollectionLoadValidationServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_it_uses_positional_mapping_when_file_has_no_header(): void
    {
        $path = $this->createCsv([
            ['FAC-200', '250000', '2026-04-10', 'REC-10', 'Cliente Uno', 'Vendedor Uno', '', 'Pago parcial'],
        ]);

        $periodControl = Mockery::mock(PeriodControlService::class);
        $periodControl->shouldReceive('assertCollectionChronology')->once();
        $periodControl->shouldReceive('assertCollectionPortfolioPrerequisite')->once();

        $service = new CollectionLoadValidationService(new SpreadsheetReader(), new ImportNormalizer(), $periodControl);

        $result = $service->validate($path);

        $this->assertTrue($result->isValid);
        $this->assertSame('2026-04', $result->periodKey);
        $this->assertSame(1, $result->validRows);
    }

    public function test_it_rejects_collection_file_when_period_cannot_be_resolved(): void
    {
        $path = $this->createCsv([
            ['documento', 'valor_pagado', 'fecha_pago'],
            ['FAC-201', '100000', ''],
        ]);

        $periodControl = Mockery::mock(PeriodControlService::class);
        $service = new CollectionLoadValidationService(new SpreadsheetReader(), new ImportNormalizer(), $periodControl);

        $result = $service->validate($path);

        $this->assertFalse($result->isValid);
        $this->assertSame('unresolved_period', $result->errors[0]->code);
    }

    private function createCsv(array $rows): string
    {
        $path = tempnam(sys_get_temp_dir(), 'mcm-collection-') . '.csv';
        $stream = fopen($path, 'wb');

        foreach ($rows as $row) {
            fputcsv($stream, $row);
        }

        fclose($stream);

        return $path;
    }
}
