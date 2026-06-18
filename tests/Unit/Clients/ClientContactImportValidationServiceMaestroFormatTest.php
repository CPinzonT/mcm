<?php

namespace Tests\Unit\Clients;

use App\Services\Clients\ClientContactImportValidationService;
use App\Services\Loads\Support\ImportNormalizer;
use App\Services\Loads\Support\SpreadsheetReader;
use PHPUnit\Framework\TestCase;

class ClientContactImportValidationServiceMaestroFormatTest extends TestCase
{
    public function test_accepts_maestro_clientes_headers(): void
    {
        $service = new ClientContactImportValidationService(new SpreadsheetReader(), new ImportNormalizer());

        $path = sys_get_temp_dir() . '/clientes-maestro-test-' . uniqid('', true) . '.csv';
        $handle = fopen($path, 'wb');
        fputcsv($handle, [
            'NIT',
            'Nombre SN',
            'Fecha de creación',
            'Canal',
            'Territorio',
            'Código de cc',
            'Límite de crédito',
            'Teléfono móvil',
            'Correo electrónico',
            'Ciudad (MM)',
            'Dirección (MM)',
        ]);
        fputcsv($handle, [
            '901276722-2',
            'COMERCIALIZADORA DISTRIYOLI S.A.S',
            '15/09/2024',
            'Distribuidor',
            '',
            'CONTADO',
            '-',
            '3186421833',
            'wpenafiel1@hotmail.com',
            'PALMIRA',
            'CARRERA 24 # 29-02',
        ]);
        fclose($handle);

        $result = $service->validate($path);

        @unlink($path);

        $this->assertTrue($result['is_valid'], json_encode($result['errors']));
        $this->assertSame('901276722-2', $result['rows'][0]['document_number']);
        $this->assertSame('COMERCIALIZADORA DISTRIYOLI S.A.S', $result['rows'][0]['name']);
        $this->assertSame('2024-09-15', $result['rows'][0]['master_created_at']);
        $this->assertSame('Distribuidor', $result['rows'][0]['channel']);
        $this->assertSame('3186421833', $result['rows'][0]['phone']);
        $this->assertSame('wpenafiel1@hotmail.com', $result['rows'][0]['email']);
        $this->assertSame('PALMIRA', $result['rows'][0]['city']);
        $this->assertSame('CARRERA 24 # 29-02', $result['rows'][0]['address']);
        $this->assertArrayNotHasKey('credit_limit', $result['rows'][0]);
    }

    public function test_requires_nit_column(): void
    {
        $service = new ClientContactImportValidationService(new SpreadsheetReader(), new ImportNormalizer());

        $path = sys_get_temp_dir() . '/clientes-sin-nit-' . uniqid('', true) . '.csv';
        $handle = fopen($path, 'wb');
        fputcsv($handle, ['Nombre SN', 'Correo electrónico']);
        fputcsv($handle, ['ACME', 'test@example.com']);
        fclose($handle);

        $result = $service->validate($path);

        @unlink($path);

        $this->assertFalse($result['is_valid']);
    }
}
