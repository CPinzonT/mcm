<?php

namespace Tests\Unit\Loads;

use App\Services\Loads\PeriodControlService;
use App\Services\Loads\PortfolioLoadValidationService;
use App\Services\Loads\Support\ImportNormalizer;
use App\Services\Loads\Support\SpreadsheetReader;
use App\Services\Risk\RiskClassificationService;
use Mockery;
use PHPUnit\Framework\TestCase;

class PortfolioLoadValidationServiceSapFormatTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_it_accepts_importe_pendiente_layout_with_full_headers(): void
    {
        $path = $this->createCsv([
            [
                '#', 'Cuenta', 'Cliente', 'NIT', 'Direccion', 'Contacto', 'Telefono', 'Canal', 'Vendedor', 'Regional',
                'NroDocumento', 'RefDocumento', 'TipoDocumento', 'FechaContabilizacion', 'FechaVencimiento',
                'ValorDocumento', 'ImportePendiente',
                'CRC_ValorARecaudar', 'MARCASMCM_ValorARecaudar', 'MARCASPRIVADAS_ValorARecaudar', 'NOAPLICA_ValorARecaudar',
                'DiasVencido', 'Actual', '1-30Dias', '31-60Dias', '61-90Dias', '91-180Dias', '181-360Dias', '+360Dias',
            ],
            [
                '1', '13050505', 'Cliente Demo', '900111111-1', 'Calle 1', 'Ana', '3001234567', 'Distribuidor', 'Asesor Uno', 'Cali',
                'DOC-1001', 'REF-1', 'FVNal3', '2026-05-20', '2026-07-04',
                '1000000', '750000',
                '750000', '0', '0', '0',
                '0', '750000', '0', '0', '0', '0', '0', '0',
            ],
        ]);

        $service = new PortfolioLoadValidationService(
            new SpreadsheetReader(),
            new ImportNormalizer(),
            Mockery::mock(PeriodControlService::class),
            new RiskClassificationService(),
        );

        $result = $service->validate($path, '2026-05');

        $this->assertTrue($result->isValid, json_encode($result->errors));
        $this->assertSame(1, $result->validRows);
        $this->assertSame(750000.0, $result->normalizedRows[0]['pending_amount']);
        $this->assertSame('CRC', $result->normalizedRows[0]['uen']);
        $this->assertSame('Asesor Uno', $result->normalizedRows[0]['sales_employee']);
        $this->assertSame(750000.0, $result->normalizedRows[0]['aging_buckets']['actual']);
    }

    public function test_it_maps_uen_from_valor_a_recaudar_columns(): void
    {
        $path = $this->createCsv([
            [
                'Cuenta', 'Cliente', 'NIT', 'GrupoCliente', 'Vendedor', 'Regional', 'NroDocumento', 'RefDocumento',
                'TipoDocumento', 'FechaContabilizacion', 'FechaVencimiento', 'FechaCreacion',
                'ValorDocumento', 'ValorARecaudar',
                'CRC_ValorARecaudar', 'MARCASMCM_ValorARecaudar', 'MARCASPRIVADAS_ValorARecaudar', 'NOAPLICA_ValorARecaudar',
                'Actual', '1-30Dias',
            ],
            [
                '13050505', 'Cliente CRC', '900111111-1', 'Distribuidor', 'Asesor Uno', 'Cali', '1001', 'REF-1',
                'FVNal3', '2026-05-20', '2026-07-04', '2026-05-20',
                '562775', '562775',
                '562775', '0', '0', '0',
                '562775', '0',
            ],
            [
                '13050505', 'Cliente NOAPLICA', '900222222-2', 'Empleados', 'Asesor Dos', 'Bogota', '1002', '',
                'NDNal', '2026-05-15', '2026-05-26', '2026-05-15',
                '984418', '984418',
                '0', '0', '0', '984418',
                '984418', '0',
            ],
        ]);

        $periodControl = Mockery::mock(PeriodControlService::class);

        $service = new PortfolioLoadValidationService(
            new SpreadsheetReader(),
            new ImportNormalizer(),
            $periodControl,
            new RiskClassificationService(),
        );

        $result = $service->validate($path, '2026-05');

        $this->assertTrue($result->isValid, json_encode($result->errors));
        $this->assertSame(2, $result->validRows);
        $this->assertSame('CRC', $result->normalizedRows[0]['uen']);
        $this->assertSame(562775.0, $result->normalizedRows[0]['pending_amount']);
        $this->assertSame('NOAPLICA', $result->normalizedRows[1]['uen']);
        $this->assertSame(984418.0, $result->normalizedRows[1]['pending_amount']);
    }

    private function createCsv(array $rows): string
    {
        $path = tempnam(sys_get_temp_dir(), 'mcm-portfolio-sap-') . '.csv';
        $stream = fopen($path, 'wb');

        foreach ($rows as $row) {
            fputcsv($stream, $row);
        }

        fclose($stream);

        return $path;
    }
}
