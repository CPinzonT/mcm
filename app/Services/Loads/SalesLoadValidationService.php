<?php

namespace App\Services\Loads;

use App\Data\Loads\LoadValidationErrorData;
use App\Data\Loads\LoadValidationResultData;
use App\Services\Loads\Support\ImportNormalizer;
use App\Services\Loads\Support\SpreadsheetReader;
use Carbon\CarbonImmutable;

class SalesLoadValidationService
{
    /**
     * Mapeo del archivo maestro de ventas (31 columnas).
     * Cruce con cartera: cliente, codigo_cliente (Código de cl), documento (No. Factura), nit.
     */
    private const HEADER_ALIASES = [
        'source_name' => ['source_name', 'source_name_1'],
        'tipo_factura' => ['tipo_factura', 'tipo_de_factura'],
        'cliente' => ['cliente', 'nombre_cliente', 'nombre_del_cliente', 'razon_social', 'client_name'],
        'producto_codigo' => ['cod_product', 'codigo_producto', 'sku', 'material', 'codigo'],
        'producto_nombre' => ['producto', 'producto_nombre', 'descripcion', 'nombre_producto', 'material_descripcion'],
        'canal' => ['canal', 'desc_canal', 'channel', 'grupo'],
        'regional' => ['regional', 'region', 'zona'],
        'vendedor' => ['vendedor', 'asesor', 'empleado_de_ventas', 'seller_name', 'sales_rep'],
        'marca_corpo' => ['marca_corpo', 'marca_corporativa', 'marca'],
        'negocio' => ['negocio', 'business'],
        'subnegocio' => ['subnegocio', 'sub_negocio'],
        'linea' => ['linea', 'line'],
        'sublinea' => ['sublinea', 'sub_linea'],
        'mes' => ['mes', 'month'],
        'proveedor' => ['proveedor', 'supplier'],
        'periodo_estado' => ['periodo', 'period'],
        'ano' => ['ano', 'anio', 'year', 'ano_1'],
        'costo' => ['costo', 'cost'],
        'utilidad_bruta' => ['ub', 'utilidad_bruta', 'gross_profit'],
        'cantidad' => ['cantidad', 'unidades', 'qty', 'quantity'],
        'valor_venta' => ['venta', 'valor_venta', 'valor', 'importe', 'monto', 'sale_amount', 'total', 'valor_neto'],
        'fecha_venta' => ['fecha', 'fecha_venta', 'fecha_factura', 'fecha_de_factura', 'invoice_date', 'sale_date'],
        'codigo_cliente' => [
            'codigo_de_cl', 'codigo_cl', 'codigo_cliente', 'cod_cliente', 'codigo_de_cliente', 'client_code',
        ],
        'codigo_proveedor' => ['cod_proveed', 'codigo_proveedor', 'cod_proveedor'],
        'origen' => ['origen', 'origin'],
        'descripcion_dir' => ['descripcion', 'descripcion_dir', 'direccion_origen'],
        'dir_destino' => ['dir_destino', 'direccion_destino', 'destino'],
        'documento' => [
            'no_factura', 'nro_factura', 'numero_factura', 'numero_documento', 'factura', 'documento',
        ],
        'lider' => ['lider', 'leader', 'jefe'],
        'uen' => ['uen', 'unidad_de_negocio', 'business_unit', 'gestion_com'],
        'gestion_comercial' => ['gestion_com', 'gestion_comercial'],
        'nit' => ['nit', 'documento_cliente', 'identificacion', 'tax_id'],
    ];

    private const MAX_STORED_ERRORS = 200;

    public function __construct(
        private readonly SpreadsheetReader $spreadsheetReader,
        private readonly ImportNormalizer $normalizer,
    ) {}

    public function validate(string $path, ?string $sourceFilename = null): LoadValidationResultData
    {
        $lookup = $this->buildAliasLookup();
        $errors = [];
        $normalizedRows = [];
        $periodKeys = [];
        $totalRows = 0;
        $emptyRows = 0;
        $skippedRows = 0;
        $headerMap = null;
        $matchedClients = 0;

        foreach ($this->spreadsheetReader->rows($path) as $row) {
            $rowNumber = $row['row_number'];
            $values = $row['values'];

            if ($headerMap === null) {
                if ($this->normalizer->isEmptyRow($values)) {
                    continue;
                }

                $candidate = $this->mapHeaders($values, $lookup);

                if ($this->hasRequiredHeaders($candidate)) {
                    $headerMap = $candidate;
                    continue;
                }

                if ($rowNumber > 40) {
                    $errors[] = LoadValidationErrorData::general(
                        'No se encontró la fila de encabezados del archivo de ventas (Cliente, Venta, Fecha o Mes/Año).',
                        'missing_headers',
                    );
                    break;
                }

                continue;
            }

            if ($this->normalizer->isEmptyRow($values)) {
                $emptyRows++;
                continue;
            }

            $totalRows++;
            $payload = $this->rowPayload($headerMap, $values);

            $saleDate = $this->resolveSaleDate($payload);
            $clientName = $this->normalizer->normalizeText($payload['cliente'] ?? null, 255);
            $clientCode = $this->normalizeClientCode($payload['codigo_cliente'] ?? null);
            $documentNumber = $this->normalizer->normalizeDocumentNumber($payload['documento'] ?? null);
            $saleAmount = $this->normalizer->parseNumber($payload['valor_venta'] ?? null);

            if ($saleDate === null) {
                $errors[] = new LoadValidationErrorData($rowNumber, 'fecha_venta', 'required', 'Fecha de venta inválida (use Fecha o Mes + Año).');
                continue;
            }

            if ($clientCode === null && $clientName === null) {
                $errors[] = new LoadValidationErrorData($rowNumber, 'cliente', 'required', 'Indica Cliente o Código de cl.');
                continue;
            }

            $rawVenta = trim((string) ($payload['valor_venta'] ?? ''));

            if ($saleAmount === null) {
                if ($rawVenta !== '' && ! in_array(strtolower($rawVenta), ['0', '0,0', '0.0', '-', 'n/a', 'na'], true)) {
                    $errors[] = new LoadValidationErrorData($rowNumber, 'valor_venta', 'invalid', 'Valor de venta (Venta) con formato no reconocido.');
                    continue;
                }

                $skippedRows++;
                continue;
            }

            if ($saleAmount == 0.0) {
                $skippedRows++;
                continue;
            }

            $periodKey = $saleDate->format('Y-m');
            $periodKeys[$periodKey] = true;

            if ($clientCode !== null || $clientName !== null) {
                $matchedClients++;
            }

            $normalizedRows[] = [
                'row_number' => $rowNumber,
                'sale_date' => $saleDate->toDateString(),
                'document_number' => $documentNumber,
                'invoice_type' => $this->normalizer->normalizeText($payload['tipo_factura'] ?? null, 40),
                'client_name' => $clientName,
                'client_code' => $clientCode,
                'client_nit' => $this->normalizer->normalizeText($payload['nit'] ?? null, 40),
                'product_code' => $this->normalizer->normalizeText($payload['producto_codigo'] ?? null, 80),
                'product_name' => $this->normalizer->normalizeText($payload['producto_nombre'] ?? null, 255),
                'quantity' => $this->normalizer->parseNumber($payload['cantidad'] ?? null),
                'sale_amount' => $saleAmount,
                'cost_amount' => $this->normalizer->parseNumber($payload['costo'] ?? null),
                'gross_profit' => $this->normalizer->parseNumber($payload['utilidad_bruta'] ?? null),
                'seller_name' => $this->normalizer->normalizeText($payload['vendedor'] ?? null, 150),
                'uen' => $this->normalizer->normalizeText($payload['uen'] ?? null, 80),
                'regional' => $this->normalizer->normalizeText($payload['regional'] ?? null, 80),
                'channel' => $this->normalizer->normalizeText($payload['canal'] ?? null, 120),
            ];
        }

        if ($headerMap === null && $errors === []) {
            $errors[] = LoadValidationErrorData::general(
                'No se encontró la fila de encabezados del archivo de ventas.',
                'missing_headers',
            );
        }

        $validRows = count($normalizedRows);
        $errorRows = count($errors);
        $periodKey = count($periodKeys) === 1 ? array_key_first($periodKeys) : null;
        $periodDate = $periodKey ? CarbonImmutable::parse($periodKey . '-01') : null;
        $isValid = $validRows > 0 && $errorRows === 0;

        return new LoadValidationResultData(
            isValid: $isValid,
            detectedModule: 'sales',
            periodKey: $periodKey,
            periodDate: $periodDate,
            normalizedRows: $normalizedRows,
            errors: array_slice($errors, 0, self::MAX_STORED_ERRORS),
            totalRows: $totalRows,
            validRows: $validRows,
            errorRows: $errorRows,
            emptyRows: $emptyRows,
            duplicateRows: 0,
            summary: [
                'source_filename' => $sourceFilename,
                'total_sale_amount' => array_sum(array_column($normalizedRows, 'sale_amount')),
                'rows_with_client_key' => $matchedClients,
                'skipped_rows' => $skippedRows,
                'format' => 'maestro_ventas_31_col',
            ],
        );
    }

    /**
     * @param  array<string, int>  $map
     */
    private function hasRequiredHeaders(array $map): bool
    {
        $hasClient = isset($map['cliente']) || isset($map['codigo_cliente']);
        $hasAmount = isset($map['valor_venta']);
        $hasDate = isset($map['fecha_venta']) || (isset($map['mes']) && isset($map['ano']));

        return $hasClient && $hasAmount && $hasDate;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function resolveSaleDate(array $payload): ?CarbonImmutable
    {
        $fromDate = $this->normalizer->parseDate($payload['fecha_venta'] ?? null);

        if ($fromDate !== null) {
            return $fromDate;
        }

        $month = $this->normalizer->parseNumber($payload['mes'] ?? null);
        $year = $this->normalizer->parseNumber($payload['ano'] ?? null);

        if ($month === null || $year === null) {
            return null;
        }

        $month = (int) $month;
        $year = (int) $year;

        if ($month < 1 || $month > 12 || $year < 1900 || $year > 2100) {
            return null;
        }

        try {
            return CarbonImmutable::create($year, $month, 1);
        } catch (\Throwable) {
            return null;
        }
    }

    private function normalizeClientCode(mixed $value): ?string
    {
        $text = strtoupper(trim((string) ($value ?? '')));

        return $text !== '' ? $text : null;
    }

    /**
     * @return array<string, string>
     */
    private function buildAliasLookup(): array
    {
        $lookup = [];

        foreach (self::HEADER_ALIASES as $canonical => $aliases) {
            foreach ($aliases as $alias) {
                $lookup[$this->normalizer->normalizeHeader($alias)] = $canonical;
            }
        }

        return $lookup;
    }

    /**
     * @param  array<int, mixed>  $values
     * @param  array<string, string>  $lookup
     * @return array<string, int>
     */
    private function mapHeaders(array $values, array $lookup): array
    {
        $map = [];

        foreach ($values as $index => $value) {
            $key = $this->normalizer->normalizeHeader((string) $value);

            if ($key !== '' && isset($lookup[$key])) {
                $map[$lookup[$key]] = (int) $index;
            }
        }

        return $map;
    }

    /**
     * @param  array<string, int>  $headerMap
     * @param  array<int, mixed>  $values
     * @return array<string, mixed>
     */
    private function rowPayload(array $headerMap, array $values): array
    {
        $payload = [];

        foreach ($headerMap as $field => $index) {
            $payload[$field] = $values[$index] ?? null;
        }

        return $payload;
    }
}
