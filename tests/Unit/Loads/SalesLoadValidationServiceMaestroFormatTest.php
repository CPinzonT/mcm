<?php

namespace Tests\Unit\Loads;

use App\Services\Loads\SalesLoadValidationService;
use App\Services\Loads\Support\ImportNormalizer;
use App\Services\Loads\Support\SpreadsheetReader;
use PHPUnit\Framework\TestCase;

class SalesLoadValidationServiceMaestroFormatTest extends TestCase
{
    public function test_accepts_maestro_ventas_headers(): void
    {
        $service = new SalesLoadValidationService(new SpreadsheetReader(), new ImportNormalizer());

        $path = sys_get_temp_dir() . '/ventas-maestro-test-' . uniqid('', true) . '.csv';
        $handle = fopen($path, 'wb');
        fputcsv($handle, [
            'Source.Name', 'Tipo factura', 'Cliente', 'Cod_Product', 'Producto', 'Canal', 'Regional',
            'Vendedor', 'Marca Corpo', 'Negocio', 'Subnegocio', 'Linea', 'Sublinea', 'Mes', 'Proveedor',
            'periodo', 'Año', 'Costo', 'UB', 'Unidades', 'Venta', 'Fecha', 'Código de cl', 'Cod Proveed',
            'Origen', 'Descripción', 'Dir Destino', 'No. Factura', 'Lider', 'UEN', 'Gestión Com',
        ]);
        fputcsv($handle, [
            'Reporte Ventas', 'Factura', 'SODIMAC CO', '20012232', 'RUBBING CO', 'Cadenas', 'BOGOTA',
            'Diana Paola', 'CRC', 'CUIDADO DE', 'AUTOS', 'COSMETICA', 'COPRADOS', '4', 'MCM COM',
            'Previo', '2024', '44291.72', '80642.52', '17', '125337.24', '21/04/2024', 'C800242106',
            'PR90983436', 'Nacional', 'AV 7 15N 66', 'CR 58 97 B SU', '20388', 'Omar Figure', 'CRC', 'CRC',
        ]);
        fclose($handle);

        $result = $service->validate($path, 'ventas-maestro.csv');

        @unlink($path);

        $this->assertTrue($result->isValid, json_encode($result->errors));
        $this->assertSame(1, $result->validRows);
        $this->assertSame('C800242106', $result->normalizedRows[0]['client_code']);
        $this->assertSame(125337.24, $result->normalizedRows[0]['sale_amount']);
        $this->assertSame('2024-04-21', $result->normalizedRows[0]['sale_date']);
    }

    public function test_skips_zero_sale_amount_without_rejecting_load(): void
    {
        $service = new SalesLoadValidationService(new SpreadsheetReader(), new ImportNormalizer());

        $path = sys_get_temp_dir() . '/ventas-cero-test-' . uniqid('', true) . '.csv';
        $handle = fopen($path, 'wb');
        fputcsv($handle, ['Cliente', 'Venta', 'Fecha', 'Código de cl']);
        fputcsv($handle, ['SODIMAC CO', '125337.24', '21/04/2024', 'C800242106']);
        fputcsv($handle, ['SODIMAC CO', '0', '21/04/2024', 'C800242106']);
        fputcsv($handle, ['SODIMAC CO', '', '21/04/2024', 'C800242106']);
        fclose($handle);

        $result = $service->validate($path, 'ventas-cero.csv');

        @unlink($path);

        $this->assertTrue($result->isValid);
        $this->assertSame(1, $result->validRows);
        $this->assertSame(0, $result->errorRows);
        $this->assertSame(2, $result->summary['skipped_rows'] ?? 0);
    }
}
