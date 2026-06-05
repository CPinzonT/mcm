<?php

namespace Tests\Unit\Loads;

use App\Services\Loads\CollectionLoadValidationService;
use App\Services\Loads\Support\ImportNormalizer;
use App\Services\Loads\Support\SpreadsheetReader;
use PHPUnit\Framework\TestCase;

class CollectionLoadValidationServiceTest extends TestCase
{
    public function test_it_uses_positional_mapping_when_file_has_no_header(): void
    {
        $path = $this->createCsv([
            ['FAC-200', '250000', '2026-04-10', 'REC-10', 'Cliente Uno', 'Vendedor Uno', '', 'Pago parcial'],
        ]);

        $service = new CollectionLoadValidationService(new SpreadsheetReader(), new ImportNormalizer());

        $result = $service->validate($path, 'recaudo_abril_2026.csv');

        $this->assertTrue($result->isValid);
        $this->assertNull($result->periodKey);
        $this->assertSame(1, $result->validRows);
    }

    public function test_it_recognizes_sap_style_headers_after_a_title_row(): void
    {
        $path = $this->createCsv([
            ['Reporte de recaudos MCM'],
            ['Nro. documento aplicado', 'Importe aplicado', 'Fecha de aplicacion', 'Nro. de recibo', 'Cliente'],
            ['FAC-900', '150000', '2026-05-15', 'REC-1', 'Cliente Demo'],
        ]);

        $service = new CollectionLoadValidationService(new SpreadsheetReader(), new ImportNormalizer());

        $result = $service->validate($path, 'Recaudomcm_21demayo.xlsx');

        $this->assertTrue($result->isValid);
        $this->assertNull($result->periodKey);
        $this->assertSame(1, $result->validRows);
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
                '0', '0', '0', 'Distribuidor', 'Bogota',
            ],
        ]);

        $service = new CollectionLoadValidationService(new SpreadsheetReader(), new ImportNormalizer());

        $result = $service->validate($path, 'ejemplo_recaudo_26demayo.xlsx');

        $this->assertTrue($result->isValid);
        $this->assertNull($result->periodKey);
        $this->assertSame(1, $result->validRows);
        $this->assertSame('575', $result->normalizedRows[0]['document_number']);
        $this->assertSame(182491.0, $result->normalizedRows[0]['amount']);
        $this->assertSame('Kelly Johanna Marino', $result->normalizedRows[0]['seller_name']);
    }

    public function test_it_prefers_importe_aplicado_uen_over_total_pago_recibido(): void
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
