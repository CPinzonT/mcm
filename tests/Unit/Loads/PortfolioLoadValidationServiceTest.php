<?php

namespace Tests\Unit\Loads;

use App\Services\Loads\PeriodControlService;
use App\Services\Loads\PortfolioLoadValidationService;
use App\Services\Loads\Support\ImportNormalizer;
use App\Services\Loads\Support\SpreadsheetReader;
use Mockery;
use PHPUnit\Framework\TestCase;

class PortfolioLoadValidationServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_it_detects_a_collection_file_loaded_in_portfolio_module(): void
    {
        $path = $this->createCsv([
            ['documento', 'valor_pagado', 'fecha_pago'],
            ['FAC-1', '100000', '2026-03-05'],
        ]);

        $periodControl = Mockery::mock(PeriodControlService::class);
        $service = new PortfolioLoadValidationService(new SpreadsheetReader(), new ImportNormalizer(), $periodControl);

        $result = $service->validate($path);

        $this->assertFalse($result->isValid);
        $this->assertSame('wrong_module', $result->errors[0]->code);
    }

    public function test_it_validates_a_portfolio_file_with_required_aliases(): void
    {
        $path = $this->createCsv([
            ['cuenta', 'cliente', 'nit', 'nro_documento', 'tipo', 'fecha_contabilizacion', 'fecha_vencimiento', 'valor_documento', 'saldo_pendiente', 'moneda'],
            ['1001', 'Cliente Demo', '900123123', 'FAC-100', 'FACTURA', '2026-03-15', '2026-03-30', '150000', '150000', 'COP'],
        ]);

        $periodControl = Mockery::mock(PeriodControlService::class);
        $periodControl->shouldReceive('assertPortfolioChronology')->once();

        $service = new PortfolioLoadValidationService(new SpreadsheetReader(), new ImportNormalizer(), $periodControl);

        $result = $service->validate($path);

        $this->assertTrue($result->isValid);
        $this->assertSame('2026-03', $result->periodKey);
        $this->assertSame(1, $result->validRows);
        $this->assertSame(0, $result->errorRows);
    }

    public function test_it_normalizes_concatenated_phone_numbers_instead_of_rejecting_the_row(): void
    {
        $path = $this->createCsv([
            ['cuenta', 'cliente', 'nit', 'telefono', 'nro_documento', 'tipo', 'fecha_contabilizacion', 'fecha_vencimiento', 'valor_documento', 'saldo_pendiente', 'moneda'],
            ['1001', 'Cliente Demo', '900123123', '3108600783-3108600783-3108600783', 'FAC-100', 'FACTURA', '2026-03-15', '2026-03-30', '150000', '150000', 'COP'],
        ]);

        $periodControl = Mockery::mock(PeriodControlService::class);
        $periodControl->shouldReceive('assertPortfolioChronology')->once();

        $service = new PortfolioLoadValidationService(new SpreadsheetReader(), new ImportNormalizer(), $periodControl);

        $result = $service->validate($path);

        $this->assertTrue($result->isValid);
        $this->assertSame('3108600783', $result->normalizedRows[0]['phone']);
        $this->assertSame(0, $result->errorRows);
    }

    public function test_it_allows_same_document_number_for_different_clients(): void
    {
        $path = $this->createCsv([
            ['cuenta', 'cliente', 'nit', 'nro_documento', 'tipo', 'fecha_contabilizacion', 'fecha_vencimiento', 'valor_documento', 'saldo_pendiente', 'moneda'],
            ['13050505', 'Cliente Uno', '900111111-1', '350094', 'AC', '2026-03-15', '2026-03-30', '100000', '100000', 'COP'],
            ['13050505', 'Cliente Dos', '900222222-2', '350094', 'AC', '2026-03-15', '2026-03-30', '200000', '200000', 'COP'],
        ]);

        $periodControl = Mockery::mock(PeriodControlService::class);
        $periodControl->shouldReceive('assertPortfolioChronology')->once();

        $service = new PortfolioLoadValidationService(new SpreadsheetReader(), new ImportNormalizer(), $periodControl);

        $result = $service->validate($path);

        $this->assertTrue($result->isValid);
        $this->assertSame(2, $result->validRows);
        $this->assertSame(0, $result->duplicateRows);
        $this->assertSame(0, $result->errorRows);
    }

    public function test_it_accepts_a_forced_cutoff_period_when_document_dates_span_multiple_months(): void
    {
        $path = $this->createCsv([
            ['cuenta', 'cliente', 'nit', 'nro_documento', 'tipo', 'fecha_contabilizacion', 'fecha_vencimiento', 'valor_documento', 'saldo_pendiente', 'moneda'],
            ['13050505', 'Cliente Uno', '900111111-1', '350094', 'AC', '2022-12-31', '2025-09-30', '100000', '100000', 'COP'],
            ['13050505', 'Cliente Dos', '900222222-2', '350095', 'AC', '2024-12-30', '2025-09-30', '200000', '200000', 'COP'],
        ]);

        $periodControl = Mockery::mock(PeriodControlService::class);
        $periodControl->shouldReceive('assertPortfolioChronology')->once();

        $service = new PortfolioLoadValidationService(new SpreadsheetReader(), new ImportNormalizer(), $periodControl);

        $result = $service->validate($path, '2025-09');

        $this->assertTrue($result->isValid);
        $this->assertSame('2025-09', $result->periodKey);
        $this->assertSame('2025-09-01', $result->normalizedRows[0]['period_date']);
        $this->assertSame('2025-09-01', $result->normalizedRows[1]['period_date']);
        $this->assertSame(0, $result->errorRows);
    }

    public function test_it_can_infer_the_cutoff_period_from_the_filename_when_document_dates_are_historical(): void
    {
        $path = $this->createCsv([
            ['cuenta', 'cliente', 'nit', 'nro_documento', 'tipo', 'fecha_contabilizacion', 'fecha_vencimiento', 'valor_documento', 'saldo_pendiente', 'moneda'],
            ['13050505', 'Cliente Uno', '900111111-1', '350094', 'AC', '2022-12-31', '2025-09-30', '100000', '100000', 'COP'],
            ['13050505', 'Cliente Dos', '900222222-2', '350095', 'AC', '2025-09-05', '2025-09-30', '200000', '200000', 'COP'],
        ]);

        $periodControl = Mockery::mock(PeriodControlService::class);
        $periodControl->shouldReceive('assertPortfolioChronology')->once();

        $service = new PortfolioLoadValidationService(new SpreadsheetReader(), new ImportNormalizer(), $periodControl);

        $result = $service->validate($path, null, 'Cartera sept 15.xlsx');

        $this->assertTrue($result->isValid);
        $this->assertSame('2025-09', $result->periodKey);
        $this->assertSame('filename_inference', data_get($result->summary, 'period_resolution.mode'));
        $this->assertSame(0, $result->errorRows);
    }

    private function createCsv(array $rows): string
    {
        $path = tempnam(sys_get_temp_dir(), 'mcm-portfolio-') . '.csv';
        $stream = fopen($path, 'wb');

        foreach ($rows as $row) {
            fputcsv($stream, $row);
        }

        fclose($stream);

        return $path;
    }
}
