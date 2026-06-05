<?php

namespace App\Services\Loads;

use App\Data\Loads\LoadValidationErrorData;
use App\Data\Loads\LoadValidationResultData;
use App\Models\PortfolioLoad;
use App\Services\Loads\Support\ImportNormalizer;
use App\Services\Loads\Support\SpreadsheetReader;

class CollectionLoadValidationService
{
    /** Encabezados SAP / plantilla MCM (CollectionImportService). */
    private const HEADER_ALIASES = [
        'documento' => [
            'documento', 'nro_documento', 'numero_documento', 'nro_documento_aplicado',
            'nro_de_documento_aplicado', 'nrodocaplicado', 'nro_doc_aplicado', 'cedula', 'nit',
        ],
        'valor_pagado' => [
            'valor_pagado', 'valor', 'importe_aplicado', 'applied_amount', 'importe',
            'valor_aplicado', 'pago', 'valor_pago', 'total_documento',
        ],
        'total_pago_recibido' => [
            'total_pago_recibido', 'totalpagorecibido', 'total_pagorecibido',
        ],
        'importe_aplicado_uen' => [
            'importe_aplicado_uen', 'importeaplicadouen', 'importe_aplicado',
        ],
        'fecha_pago' => [
            'fecha_aplicacion', 'fecha_de_aplicacion', 'fechaaplicacion', 'application_date',
            'fecha_pago', 'payment_date', 'fecha_recibo', 'fecha_de_recibo', 'fecharecibo', 'fecha',
        ],
        'nro_recibo' => [
            'nro_recibo', 'recibo', 'nro_de_recibo', 'receipt_number', 'numero_recibo',
            'comprobante', 'nrorecibo', 'nro_de_recibo',
        ],
        'id_reconciliacion' => [
            'id_reconciliacion', 'idreconciliacion', 'id_de_reconciliacion',
        ],
        'tipo_doc' => [
            'tipo_documento_aplicado', 'tipo_documento', 'tipo', 'tipo_doc',
            'tipodocaplicado', 'tipo_doc_aplicado',
        ],
        'cliente' => ['cliente', 'client_name', 'nombre_cliente'],
        'vendedor' => ['vendedor', 'asesor', 'seller_name', 'empleado_de_ventas'],
        'uen' => ['uen', 'unidad_de_negocio'],
        'grupo' => ['grupo', 'grupo_cliente', 'channel', 'canal'],
        'regional' => ['regional', 'region'],
        'observacion' => ['observacion', 'observaciones', 'notes', 'detalle', 'descripcion'],
    ];

    private const PORTFOLIO_HEADER_ALIASES = [
        'cuenta' => ['cuenta', 'account'],
        'saldo_pendiente' => ['saldo_pendiente', 'saldo', 'pending_amount'],
        'fecha_contabilizacion' => ['fecha_contabilizacion', 'fecha_contable', 'posting_date'],
    ];

    private const MAX_STORED_ERRORS = 500;

    private const HEADER_SCAN_LIMIT = 40;

    public function __construct(
        private readonly SpreadsheetReader $spreadsheetReader,
        private readonly ImportNormalizer $normalizer,
    ) {}

    public function validate(string $path, ?string $sourceFilename = null): LoadValidationResultData
    {
        $collectionLookup = $this->buildAliasLookup(self::HEADER_ALIASES);
        $portfolioLookup = $this->buildAliasLookup(self::PORTFOLIO_HEADER_ALIASES);

        $errors = [];
        $normalizedRows = [];
        $seenDuplicates = [];
        $totalRows = 0;
        $emptyRows = 0;
        $duplicateRows = 0;
        $headerMap = null;
        $usesHeader = false;

        foreach ($this->spreadsheetReader->rows($path) as $row) {
            $rowNumber = $row['row_number'];
            $values = $row['values'];

            if ($headerMap === null) {
                if ($this->normalizer->isEmptyRow($values)) {
                    continue;
                }

                $candidateMap = $this->mapHeaders($values, $collectionLookup);
                $portfolioHeaderMap = $this->mapHeaders($values, $portfolioLookup);

                if (count($portfolioHeaderMap) >= 2 && ! $this->hasRequiredCollectionHeaders($candidateMap)) {
                    $errors[] = LoadValidationErrorData::general(
                        'El archivo parece corresponder al modulo de cartera. Use la carga de cartera para este archivo.',
                        'wrong_module'
                    );
                    break;
                }

                if ($this->hasRequiredCollectionHeaders($candidateMap)) {
                    $headerMap = $candidateMap;
                    $usesHeader = true;
                    continue;
                }

                if ($rowNumber >= self::HEADER_SCAN_LIMIT) {
                    $errors[] = LoadValidationErrorData::general(
                        'No se reconocio el encabezado de recaudos (se esperan columnas como Nro. documento aplicado, Importe aplicado y Fecha de recibo/aplicacion).',
                        'header_not_found'
                    );
                    break;
                }

                continue;
            }

            $totalRows++;

            if ($this->normalizer->isEmptyRow($values)) {
                $emptyRows++;
                continue;
            }

            $payload = $this->rowPayload($headerMap, $values);
            $rowErrors = [];
            $normalized = $this->normalizeRow($rowNumber, $payload, $rowErrors);

            if ($normalized !== null) {
                $duplicateKey = implode('|', [
                    $normalized['document_number'],
                    $normalized['client_name'] ?? '',
                    $normalized['seller_name'] ?? '',
                    $normalized['amount'],
                    $normalized['payment_date'] ?? 'no-date',
                    $normalized['receipt_number'] ?? 'no-receipt',
                ]);

                if (isset($seenDuplicates[$duplicateKey])) {
                    $duplicateRows++;
                    $emptyRows++;
                    $normalized = null;
                } else {
                    $seenDuplicates[$duplicateKey] = true;
                }
            }

            if ($rowErrors !== []) {
                foreach ($rowErrors as $rowError) {
                    if (count($errors) < self::MAX_STORED_ERRORS) {
                        $errors[] = $rowError;
                    }
                }
                continue;
            }

            if ($normalized === null) {
                $emptyRows++;
                continue;
            }

            $normalizedRows[] = $normalized;
        }

        if ($headerMap === null && $errors === []) {
            $errors[] = LoadValidationErrorData::general('El archivo de recaudos esta vacio o no contiene un encabezado util.', 'empty_file');
        }

        if ($normalizedRows !== [] && $errors === []) {
            $hasActivePortfolio = PortfolioLoad::query()
                ->where('status', 'completed')
                ->where('is_active', true)
                ->exists();

            if (! $hasActivePortfolio) {
                $errors[] = LoadValidationErrorData::general(
                    'Antes de cargar recaudos debe existir una carga de cartera activa y completada.',
                    'no_active_portfolio',
                );
            }
        }

        if ($normalizedRows === [] && $errors === []) {
            $errors[] = LoadValidationErrorData::general(
                'No se encontraron filas validas para recaudos despues de analizar el archivo.',
                'no_valid_rows'
            );
        }

        if ($normalizedRows === [] && $errors !== [] && count($errors) >= self::MAX_STORED_ERRORS) {
            $errors[] = LoadValidationErrorData::general(
                'Demasiados errores por fila; revise el encabezado y el formato del archivo.',
                'too_many_row_errors'
            );
        }

        return new LoadValidationResultData(
            isValid: $errors === [] && $normalizedRows !== [],
            detectedModule: 'collection',
            periodKey: null,
            periodDate: null,
            normalizedRows: $normalizedRows,
            errors: $errors,
            totalRows: $totalRows,
            validRows: count($normalizedRows),
            errorRows: $this->countErrorRows($errors, $totalRows, count($normalizedRows)),
            emptyRows: $emptyRows,
            duplicateRows: $duplicateRows,
            summary: [
                'rules' => [
                    'Carga atomica: si existen errores, la version no se activa.',
                    'Busca encabezado SAP en las primeras filas (no solo la fila 1).',
                    'Cruce contra la cartera activa por factura, cliente y vendedor.',
                ],
                'uses_header' => $usesHeader,
            ],
        );
    }

    /**
     * @param  array<string, int>  $map
     */
    private function hasRequiredCollectionHeaders(array $map): bool
    {
        $hasAmount = isset($map['valor_pagado'])
            || isset($map['importe_aplicado_uen'])
            || isset($map['total_pago_recibido']);

        return isset($map['documento']) && $hasAmount;
    }

    private function rowPayload(array $headerMap, array $values): array
    {
        $payload = [];

        foreach ($headerMap as $field => $index) {
            $payload[$field] = $values[$index] ?? null;
        }

        return $payload;
    }

    private function normalizeRow(int $rowNumber, array $payload, array &$rowErrors): ?array
    {
        $documentNumber = $this->normalizer->normalizeDocumentNumber($payload['documento'] ?? null);

        if ($documentNumber === null) {
            return null;
        }

        $amount = $this->resolveRowAmount($payload);

        if ($amount === null || abs($amount) < 0.0001) {
            return null;
        }

        $paymentDate = $this->normalizer->parseDate($payload['fecha_pago'] ?? null);

        $fechaRaw = trim((string) ($payload['fecha_pago'] ?? ''));
        if ($fechaRaw !== '' && $paymentDate === null) {
            $rowErrors[] = new LoadValidationErrorData($rowNumber, 'fecha_pago', 'invalid_date', 'La fecha de pago no es valida.');
        }

        if ($rowErrors !== []) {
            return null;
        }

        return [
            'row_number' => $rowNumber,
            'document_number' => $documentNumber,
            'amount' => (float) $amount,
            'payment_date' => $paymentDate?->toDateString(),
            'receipt_number' => $this->normalizer->normalizeText($payload['nro_recibo'] ?? null, 100),
            'document_type' => $this->normalizer->normalizeText($payload['tipo_doc'] ?? null, 50),
            'client_name' => $this->normalizer->normalizeText($payload['cliente'] ?? null, 255),
            'seller_name' => $this->normalizer->normalizeText($payload['vendedor'] ?? null, 120),
            'notes' => $this->normalizer->normalizeText($payload['observacion'] ?? null, 1000),
            'reconciliation_id' => $this->normalizer->normalizeText($payload['id_reconciliacion'] ?? null, 100),
            'uen' => $this->normalizer->normalizeText($payload['uen'] ?? null, 50),
            'channel' => $this->normalizer->normalizeText($payload['grupo'] ?? null, 100),
            'regional' => $this->normalizer->normalizeText($payload['regional'] ?? null, 100),
            'source_payload' => $payload,
        ];
    }

    private function resolveRowAmount(array $payload): ?float
    {
        $importeUen = $this->normalizer->parseNumber($payload['importe_aplicado_uen'] ?? null);
        $totalPago = $this->normalizer->parseNumber($payload['total_pago_recibido'] ?? null);
        $legacy = $this->normalizer->parseNumber($payload['valor_pagado'] ?? null);

        if ($importeUen !== null && abs($importeUen) >= 0.0001) {
            return $importeUen;
        }

        if ($totalPago !== null && abs($totalPago) >= 0.0001) {
            return $totalPago;
        }

        if ($legacy !== null && abs($legacy) >= 0.0001) {
            return $legacy;
        }

        return null;
    }

    private function buildAliasLookup(array $aliases): array
    {
        $lookup = [];

        foreach ($aliases as $canonical => $variants) {
            foreach ($variants as $variant) {
                $lookup[$this->normalizer->normalizeHeader($variant)] = $canonical;
            }
        }

        return $lookup;
    }

    private function mapHeaders(array $row, array $lookup): array
    {
        $map = [];

        foreach ($row as $index => $value) {
            $normalized = $this->normalizer->normalizeHeader((string) $value);

            if ($normalized === '' || ! isset($lookup[$normalized])) {
                continue;
            }

            $canonical = $lookup[$normalized];
            $map[$canonical] ??= $index;
        }

        return $map;
    }

    private function countErrorRows(array $errors, int $totalRows, int $validRows): int
    {
        if ($validRows === 0 && $totalRows > 0) {
            return $totalRows;
        }

        $keys = [];

        foreach ($errors as $error) {
            $keys[] = $error->rowNumber !== null ? "row:{$error->rowNumber}" : 'general:' . $error->code . ':' . $error->message;
        }

        return count(array_unique($keys));
    }
}
