<?php

namespace App\Services\Loads;

use App\Data\Loads\LoadValidationErrorData;
use App\Data\Loads\LoadValidationResultData;
use App\Services\Loads\Support\ImportNormalizer;
use App\Services\Loads\Support\SpreadsheetReader;
use DomainException;

class CollectionLoadValidationService
{
    private const HEADER_ALIASES = [
        'documento' => ['documento', 'nro_documento', 'numero_documento', 'nro_documento_aplicado', 'cedula', 'nit'],
        'valor_pagado' => ['valor_pagado', 'valor', 'importe_aplicado', 'applied_amount', 'importe', 'valor_aplicado', 'pago', 'valor_pago'],
        'fecha_pago' => ['fecha_pago', 'payment_date', 'fecha_recibo', 'fecha_de_recibo', 'fecha_aplicacion', 'fecha_de_aplicacion', 'fecha'],
        'periodo' => ['periodo', 'period', 'mes', 'periodo_recaudo'],
        'nro_recibo' => ['nro_recibo', 'recibo', 'nro_de_recibo', 'receipt_number', 'numero_recibo', 'comprobante'],
        'tipo_doc' => ['tipo_documento_aplicado', 'tipo_documento', 'tipo'],
        'cliente' => ['cliente', 'client_name', 'nombre_cliente'],
        'vendedor' => ['vendedor', 'asesor', 'seller_name', 'empleado_de_ventas'],
        'observacion' => ['observacion', 'observaciones', 'notes', 'detalle', 'descripcion'],
    ];

    private const PORTFOLIO_HEADER_ALIASES = [
        'cuenta' => ['cuenta', 'account'],
        'saldo_pendiente' => ['saldo_pendiente', 'saldo', 'pending_amount'],
        'fecha_contabilizacion' => ['fecha_contabilizacion', 'fecha_contable', 'posting_date'],
    ];

    public function __construct(
        private readonly SpreadsheetReader $spreadsheetReader,
        private readonly ImportNormalizer $normalizer,
        private readonly PeriodControlService $periodControlService,
    ) {}

    public function validate(string $path): LoadValidationResultData
    {
        $collectionLookup = $this->buildAliasLookup(self::HEADER_ALIASES);
        $portfolioLookup = $this->buildAliasLookup(self::PORTFOLIO_HEADER_ALIASES);

        $headerMap = [];
        $errors = [];
        $normalizedRows = [];
        $candidatePeriods = [];
        $seenDuplicates = [];
        $totalRows = 0;
        $emptyRows = 0;
        $duplicateRows = 0;
        $firstRowHandled = false;
        $usesHeader = true;

        foreach ($this->spreadsheetReader->rows($path) as $row) {
            $rowNumber = $row['row_number'];
            $values = $row['values'];

            if (! $firstRowHandled) {
                $firstRowHandled = true;

                if ($this->normalizer->isEmptyRow($values)) {
                    $errors[] = LoadValidationErrorData::general('El archivo de recaudos esta vacio.', 'empty_file');
                    break;
                }

                $headerMap = $this->mapHeaders($values, $collectionLookup);
                $portfolioHeaderMap = $this->mapHeaders($values, $portfolioLookup);
                $usesHeader = isset($headerMap['documento'], $headerMap['valor_pagado']);

                if (count($portfolioHeaderMap) >= 2 && ! $usesHeader) {
                    $errors[] = LoadValidationErrorData::general(
                        'El archivo parece corresponder al modulo de cartera. Use la carga de cartera para este archivo.',
                        'wrong_module'
                    );

                    break;
                }

                if (! $usesHeader) {
                    $headerMap = [
                        'documento' => 0,
                        'valor_pagado' => 1,
                        'fecha_pago' => 2,
                        'nro_recibo' => 3,
                        'cliente' => 4,
                        'vendedor' => 5,
                        'periodo' => 6,
                        'observacion' => 7,
                    ];
                } else {
                    continue;
                }
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
                array_push($errors, ...$rowErrors);
                continue;
            }

            if ($normalized === null) {
                $emptyRows++;
                continue;
            }

            if ($normalized['row_period_key'] !== null) {
                $candidatePeriods[$normalized['row_period_key']] = ($candidatePeriods[$normalized['row_period_key']] ?? 0) + 1;
            }

            $normalizedRows[] = $normalized;
        }

        if (! $firstRowHandled) {
            $errors[] = LoadValidationErrorData::general('El archivo de recaudos no contiene filas para analizar.', 'empty_file');
        }

        $periodKey = null;
        $periodDate = null;

        if ($normalizedRows !== [] && $errors === []) {
            $periodKey = $this->resolveFinalPeriodKey($candidatePeriods);
            $periodDate = $this->normalizer->firstDayOfPeriod($periodKey);

            $normalizedRows = array_map(static function (array $row) use ($periodKey, $periodDate): array {
                $row['period_key'] = $periodKey;
                $row['period_date'] = $periodDate->toDateString();
                return $row;
            }, $normalizedRows);

            try {
                $this->periodControlService->assertCollectionChronology($periodDate);
            } catch (DomainException $exception) {
                $errors[] = LoadValidationErrorData::general($exception->getMessage(), 'period_control_blocked');
            }
        }

        if ($normalizedRows === [] && $errors === []) {
            $errors[] = LoadValidationErrorData::general(
                'No se encontraron filas validas para recaudos despues de analizar el archivo.',
                'no_valid_rows'
            );
        }

        return new LoadValidationResultData(
            isValid: $errors === [] && $normalizedRows !== [],
            detectedModule: 'collection',
            periodKey: $periodKey,
            periodDate: $periodDate,
            normalizedRows: $normalizedRows,
            errors: $errors,
            totalRows: $totalRows,
            validRows: count($normalizedRows),
            errorRows: $this->countErrorRows($errors),
            emptyRows: $emptyRows,
            duplicateRows: $duplicateRows,
            summary: [
                'rules' => [
                    'Carga atomica: si existen errores, la version no se activa.',
                    'El periodo final se resuelve por mayoria entre periodo explicito y fecha de pago.',
                    'Si una fila no trae periodo ni fecha, hereda el periodo mayoritario de las filas validas.',
                ],
                'uses_header' => $usesHeader,
            ],
        );
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

        // Fila sin documento: se salta silenciosamente (igual que mcmdef)
        if ($documentNumber === null) {
            return null;
        }

        $amount = $this->normalizer->parseNumber($payload['valor_pagado'] ?? null);

        if ($amount === null) {
            $rowErrors[] = new LoadValidationErrorData($rowNumber, 'valor_pagado', 'invalid_number', 'El valor pagado debe ser numerico y obligatorio.');
        }

        $paymentDate = $this->normalizer->parseDate($payload['fecha_pago'] ?? null);
        $explicitPeriod = $this->normalizer->parseMonthKey($payload['periodo'] ?? null);

        if (($payload['periodo'] ?? null) && $explicitPeriod === null) {
            $rowErrors[] = new LoadValidationErrorData($rowNumber, 'periodo', 'invalid_period', 'El periodo informado en la fila no tiene un formato valido.');
        }

        if (($payload['fecha_pago'] ?? null) && $paymentDate === null) {
            $rowErrors[] = new LoadValidationErrorData($rowNumber, 'fecha_pago', 'invalid_date', 'La fecha de pago no es valida.');
        }

        if ($rowErrors !== []) {
            return null;
        }

        $rowPeriodKey = $explicitPeriod ?? $paymentDate?->format('Y-m');

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
            'row_period_key' => $rowPeriodKey,
            'source_payload' => $payload,
        ];
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

    private function resolveFinalPeriodKey(array $candidatePeriods): string
    {
        if ($candidatePeriods === []) {
            return now()->format('Y-m');
        }

        arsort($candidatePeriods);
        return (string) array_key_first($candidatePeriods);
    }

    private function countErrorRows(array $errors): int
    {
        $keys = [];

        foreach ($errors as $error) {
            $keys[] = $error->rowNumber !== null ? "row:{$error->rowNumber}" : 'general:' . $error->code . ':' . $error->message;
        }

        return count(array_unique($keys));
    }
}
