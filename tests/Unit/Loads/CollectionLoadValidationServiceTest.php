<?php

namespace Tests\Unit\Loads;

use App\Services\Loads\CollectionLoadValidationService;
use App\Services\Loads\Support\ImportNormalizer;
use App\Services\Loads\Support\SpreadsheetReader;
use PHPUnit\Framework\TestCase;

class CollectionLoadValidationServiceTest extends TestCase
{
    public function test_it_rejects_a_file_without_the_required_header(): void
    {
        $path = $this->createCsv([
            ['FAC-200', '250000', '2026-04-10', 'REC-10', 'Cliente Uno', 'Vendedor Uno', '', 'Pago parcial'],
        ]);

        $service = new CollectionLoadValidationService(new SpreadsheetReader(), new ImportNormalizer());

        $result = $service->validate($path, 'recaudo_abril_2026.csv');

        $this->assertFalse($result->isValid);
        $this->assertNull($result->periodKey);
        $this->assertSame(0, $result->validRows);
    }

    public function test_it_rejects_legacy_importe_aplicado_without_uen_column(): void
    {
        $path = $this->createCsv([
            ['Reporte de recaudos MCM'],
            ['Nro. documento aplicado', 'Importe aplicado', 'Fecha de aplicacion', 'Nro. de recibo', 'Cliente'],
            ['FAC-900', '150000', '2026-05-15', 'REC-1', 'Cliente Demo'],
        ]);

        $service = new CollectionLoadValidationService(new SpreadsheetReader(), new ImportNormalizer());

        $result = $service->validate($path, 'Recaudomcm_21demayo.xlsx');

        $this->assertFalse($result->isValid);
        $this->assertNull($result->periodKey);
        $this->assertSame('missing_importe_aplicado_uen', $result->errors[0]->code);
    }

    public function test_it_recognizes_sap_collection_export_with_nro_doc_aplicado_and_total_pago(): void
    {
        $path = $this->createCsv([
            [
                '#', 'NroRecibo', 'FechaRecibo', 'TotalPagoRecibido', 'IDReconciliacion', 'Cliente', 'Vendedor',
                'TipoDocAplicado', 'NroDocAplicado', 'FechaVencimiento', 'FechaAplicacion', 'UEN',
                'TotalVentaUEN', 'ImporteAplicadoUEN', 'SaldoPendienteUEN', 'Grupo', 'Regional',
            ],
            [
                '1', '10', '02/01/2023', '182491', '478', 'TECNOLUBRICANTES DEL LLANO LTDA',
                'Kelly Johanna Marino', 'Factura de Cliente', '575', '31/01/2023', '18/01/2023', '',
                '0', '182491', '0', 'Distribuidor', 'Bogota',
            ],
        ]);

        $service = new CollectionLoadValidationService(new SpreadsheetReader(), new ImportNormalizer());

        $result = $service->validate($path, 'ejemplo_recaudo_26demayo.xlsx');

        $this->assertTrue($result->isValid);
        $this->assertNull($result->periodKey);
        $this->assertSame(1, $result->validRows);
        $this->assertSame('575', $result->normalizedRows[0]['document_number']);
        $this->assertSame(182491.0, $result->normalizedRows[0]['amount']);
        $this->assertSame('2023-01-18', $result->normalizedRows[0]['payment_date']);
        $this->assertSame('Kelly Johanna Marino', $result->normalizedRows[0]['seller_name']);
    }

    public function test_it_uses_only_importe_aplicado_uen(): void
    {
        $path = $this->createCsv([
            ['NroDocAplicado', 'ImporteAplicadoUEN', 'TotalPagoRecibido', 'FechaAplicacion', 'Cliente'],
            ['900', '50000', '99999', '2026-05-10', 'Cliente Demo'],
        ]);

        $service = new CollectionLoadValidationService(new SpreadsheetReader(), new ImportNormalizer());

        $result = $service->validate($path);

        $this->assertTrue($result->isValid);
        $this->assertSame(50000.0, $result->normalizedRows[0]['amount']);
    }

    public function test_it_preserves_repeated_rows_from_the_excel_file(): void
    {
        $header = ['NroDocAplicado', 'ImporteAplicadoUEN', 'FechaAplicacion', 'Cliente'];
        $row = ['900', '50000', '2026-05-10', 'Cliente Demo'];
        $path = $this->createCsv([$header, $row, $row]);

        $service = new CollectionLoadValidationService(new SpreadsheetReader(), new ImportNormalizer());

        $result = $service->validate($path);

        $this->assertTrue($result->isValid);
        $this->assertSame(2, $result->validRows);
        $this->assertSame(0, $result->duplicateRows);
        $this->assertSame(100000.0, array_sum(array_column($result->normalizedRows, 'amount')));
    }

    public function test_it_requires_fecha_aplicacion_instead_of_fecha_recibo(): void
    {
        $path = $this->createCsv([
            ['NroDocAplicado', 'ImporteAplicadoUEN', 'FechaRecibo'],
            ['900', '50000', '2026-05-10'],
        ]);

        $service = new CollectionLoadValidationService(new SpreadsheetReader(), new ImportNormalizer());

        $result = $service->validate($path);

        $this->assertFalse($result->isValid);
        $this->assertSame('missing_fecha_aplicacion', $result->errors[0]->code);
    }

    public function test_it_rejects_collection_file_when_header_is_missing(): void
    {
        $path = $this->createCsv([
            ['col_a', 'col_b', 'col_c'],
            ['x', 'y', 'z'],
        ]);

        $service = new CollectionLoadValidationService(new SpreadsheetReader(), new ImportNormalizer());

        $result = $service->validate($path);

        $this->assertFalse($result->isValid);
        $this->assertSame('header_not_found', $result->errors[0]->code);
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
