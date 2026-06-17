<?php

namespace App\Services\Loads;

use App\Data\Loads\LoadValidationErrorData;
use App\Data\Loads\LoadValidationResultData;
use App\Services\Loads\Support\ImportNormalizer;
use App\Services\Loads\Support\SpreadsheetReader;
use Carbon\CarbonImmutable;

class SalesLoadValidationService
{
  private const HEADER_ALIASES = [
        'fecha_venta' => [
            'fecha_venta', 'fecha', 'fecha_factura', 'fecha_de_factura', 'invoice_date', 'sale_date',
        ],
        'documento' => [
            'documento', 'no_factura', 'nro_factura', 'numero_documento', 'numero_factura', 'factura',
        ],
        'cliente' => [
            'cliente', 'nombre_cliente', 'nombre_del_cliente', 'razon_social', 'client_name',
        ],
        'nit' => ['nit', 'documento_cliente', 'identificacion', 'tax_id'],
        'producto_codigo' => ['producto_codigo', 'codigo_producto', 'sku', 'material', 'codigo'],
        'producto_nombre' => ['producto', 'producto_nombre', 'descripcion', 'nombre_producto', 'material_descripcion'],
        'cantidad' => ['cantidad', 'qty', 'quantity', 'unidades'],
        'valor_venta' => [
            'valor_venta', 'valor', 'importe', 'monto', 'venta', 'sale_amount', 'total', 'valor_neto',
        ],
        'vendedor' => ['vendedor', 'asesor', 'empleado_de_ventas', 'seller_name', 'sales_rep'],
        'uen' => ['uen', 'unidad_de_negocio', 'business_unit'],
        'regional' => ['regional', 'region'],
        'canal' => ['canal', 'desc_canal', 'channel', 'grupo'],
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
        $headerMap = null;

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
                        'No se encontró la fila de encabezados de ventas (fecha, documento, cliente, valor).',
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

            $saleDate = $this->normalizer->parseDate($payload['fecha_venta'] ?? null);
            $documentNumber = $this->normalizer->normalizeDocumentNumber($payload['documento'] ?? null);
            $clientName = $this->normalizer->normalizeText($payload['cliente'] ?? null, 255);
            $saleAmount = $this->normalizer->parseNumber($payload['valor_venta'] ?? null);

            if ($saleDate === null) {
                $errors[] = new LoadValidationErrorData($rowNumber, 'fecha_venta', 'required', 'Fecha de venta inválida o vacía.');
                continue;
            }

            if ($documentNumber === null && $clientName === null) {
                $errors[] = new LoadValidationErrorData($rowNumber, 'cliente', 'required', 'Indica cliente o número de documento.');
                continue;
            }

            if ($saleAmount === null || $saleAmount == 0.0) {
                $errors[] = new LoadValidationErrorData($rowNumber, 'valor_venta', 'required', 'Valor de venta inválido o cero.');
                continue;
            }

            $periodKey = $saleDate->format('Y-m');
            $periodKeys[$periodKey] = true;

            $normalizedRows[] = [
                'row_number' => $rowNumber,
                'sale_date' => $saleDate->toDateString(),
                'document_number' => $documentNumber,
                'client_name' => $clientName,
                'client_nit' => $this->normalizer->normalizeText($payload['nit'] ?? null, 40),
                'product_code' => $this->normalizer->normalizeText($payload['producto_codigo'] ?? null, 80),
                'product_name' => $this->normalizer->normalizeText($payload['producto_nombre'] ?? null, 255),
                'quantity' => $this->normalizer->parseNumber($payload['cantidad'] ?? null),
                'sale_amount' => $saleAmount,
                'seller_name' => $this->normalizer->normalizeText($payload['vendedor'] ?? null, 150),
                'uen' => $this->normalizer->normalizeText($payload['uen'] ?? null, 80),
                'regional' => $this->normalizer->normalizeText($payload['regional'] ?? null, 80),
                'channel' => $this->normalizer->normalizeText($payload['canal'] ?? null, 120),
            ];
        }

        if ($headerMap === null && $errors === []) {
            $errors[] = LoadValidationErrorData::general(
                'No se encontró la fila de encabezados de ventas.',
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
            ],
        );
    }

    /**
     * @param  array<string, int>  $map
     */
    private function hasRequiredHeaders(array $map): bool
    {
        return isset($map['fecha_venta'], $map['valor_venta'])
            && (isset($map['documento']) || isset($map['cliente']));
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
