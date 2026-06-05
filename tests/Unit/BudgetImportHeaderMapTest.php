<?php

namespace Tests\Unit;

use App\Services\Budget\BudgetImportService;
use App\Services\Loads\Support\ImportNormalizer;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class BudgetImportHeaderMapTest extends TestCase
{
    public function test_maps_user_budget_headers(): void
    {
        $headers = [
            'NOMBRE CLIENTE',
            'REGIONAL',
            'DESC CANAL',
            'VENDEDOR',
            'TIPO TRANSACCION',
            'NO FACTURA',
            'FECHA FACTURA',
            'FECHA VENCIMIENTO',
            'DIAS VENCIMIENTO',
            'MONTO INICIAL',
            'SALDO DEBIDO',
            '1-90',
            '>90',
            'SIN VENCER',
            'ROTACION',
            'PPTO',
            'RECAUDO',
            'CATEGORIAS',
            'Fecha de aplicación',
            'Column19',
        ];

        $map = $this->mapHeaders($headers);

        $this->assertArrayHasKey('nombre_cliente', $map);
        $this->assertArrayHasKey('regional', $map);
        $this->assertArrayHasKey('desc_canal', $map);
        $this->assertArrayHasKey('vendedor', $map);
        $this->assertArrayHasKey('tipo_transac', $map);
        $this->assertArrayHasKey('ppto', $map);
        $this->assertArrayHasKey('recaudo', $map);
        $this->assertArrayHasKey('aging_1_90', $map);
        $this->assertArrayHasKey('aging_over_90', $map);
        $this->assertArrayHasKey('fecha_aplicacion', $map);
        $this->assertSame(15, $map['ppto']);
        $this->assertSame(16, $map['recaudo']);
    }

    /**
     * @param  list<string>  $headers
     * @return array<string, int>
     */
    private function mapHeaders(array $headers): array
    {
        $service = new BudgetImportService(
            new \App\Services\Loads\Support\SpreadsheetReader,
            new ImportNormalizer,
        );

        $build = new ReflectionMethod(BudgetImportService::class, 'buildAliasLookup');
        $build->setAccessible(true);
        $lookup = $build->invoke($service);

        $mapHeaders = new ReflectionMethod(BudgetImportService::class, 'mapHeaders');
        $mapHeaders->setAccessible(true);

        return $mapHeaders->invoke($service, $headers, $lookup);
    }
}
