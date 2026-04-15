<?php

namespace App\Services\Loads;

use App\Data\Loads\LoadValidationErrorData;
use App\Data\Loads\LoadValidationResultData;
use App\Services\Loads\Support\ImportNormalizer;
use App\Services\Loads\Support\SpreadsheetReader;
use Carbon\CarbonImmutable;
use DomainException;
use Illuminate\Support\Str;

class PortfolioLoadValidationService
{
    private const REQUIRED_HEADERS = [
        'cuenta',
        'cliente',
        'nit',
        'nro_documento',
        'tipo',
        'fecha_contabilizacion',
        'fecha_vencimiento',
        'valor_documento',
        'saldo_pendiente',
        'moneda',
    ];

    private const HEADER_ALIASES = [
        'cuenta' => ['cuenta', 'account'],
        'cliente' => ['cliente', 'razon_social', 'nombre_cliente', 'customer'],
        'nit' => ['nit', 'identificacion', 'documento_cliente'],
        'direccion' => ['direccion', 'direccion_cliente', 'address'],
        'contacto' => ['contacto', 'contact_name', 'contacto_cliente'],
        'telefono' => ['telefono', 'telefonos', 'phone', 'telefono_cliente'],
        'canal' => ['canal', 'channel', 'grupo'],
        'uens' => ['uens', 'uen', 'unidad_negocio'],
        'empleado_de_ventas' => ['empleado_de_ventas', 'empleado_de_ventas_', 'empleado_ventas', 'vendedor', 'asesor', 'sales_employee'],
        'regional' => ['regional', 'region'],
        'nro_documento' => ['nro_documento', 'nro_documento_', 'documento', 'numero_documento', 'nro_doc'],
        'nro_ref_de_cliente' => ['nro_ref_de_cliente', 'referencia_cliente', 'nro_referencia_cliente', 'customer_reference'],
        'tipo' => ['tipo', 'tipo_documento', 'document_type'],
        'fecha_contabilizacion' => ['fecha_contabilizacion', 'fecha_contable', 'fecha_documento', 'posting_date'],
        'fecha_activacion' => ['fecha_activacion', 'activation_date'],
        'fecha_vencimiento' => ['fecha_vencimiento', 'due_date', 'vencimiento'],
        'valor_documento' => ['valor_documento', 'valor', 'monto_documento', 'original_amount'],
        'saldo_pendiente' => ['saldo_pendiente', 'saldo', 'saldo_actual', 'pending_amount'],
        'moneda' => ['moneda', 'currency'],
        'dias_vencido' => ['dias_vencido', 'dias_mora', 'days_overdue'],
        'actual' => ['actual', 'corriente', 'bucket_actual'],
        '1_30_dias' => ['1_30_dias', '1_30', '1_30dias', 'bucket_1_30'],
        '31_60_dias' => ['31_60_dias', '31_60', '31_60dias', 'bucket_31_60'],
        '61_90_dias' => ['61_90_dias', '61_90', '61_90dias', 'bucket_61_90'],
        '91_180_dias' => ['91_180_dias', '91_180', '91_180dias', 'bucket_91_180'],
        '181_360_dias' => ['181_360_dias', '181_360', '181_360dias', 'bucket_181_360'],
        '361_dias' => ['361_dias', '361', '361_mas', 'mas_361', 'bucket_361'],
    ];

    private const COLLECTION_HEADER_ALIASES = [
        'documento' => ['documento', 'numero_documento', 'nro_documento'],
        'valor_pagado' => ['valor_pagado', 'valor', 'importe_aplicado', 'applied_amount', 'total_pago_recibido'],
        'fecha_pago' => ['fecha_pago', 'payment_date', 'fecha_de_recibo'],
    ];

    private const MAX_LENGTHS = [
        'cuenta' => 50,
        'cliente' => 255,
        'nit' => 30,
        'direccion' => 255,
        'contacto' => 120,
        'telefono' => 30,
        'canal' => 100,
        'uens' => 100,
        'empleado_de_ventas' => 120,
        'regional' => 100,
        'nro_documento' => 100,
        'nro_ref_de_cliente' => 100,
        'tipo' => 50,
        'moneda' => 10,
    ];

    public function __construct(
        private readonly SpreadsheetReader $spreadsheetReader,
        private readonly ImportNormalizer $normalizer,
        private readonly PeriodControlService $periodControlService,
    ) {}

    public function validate(string $path, ?string $forcedPeriodKey = null, ?string $sourceName = null): LoadValidationResultData
    {
        $aliasLookup = $this->buildAliasLookup(self::HEADER_ALIASES);
        $collectionLookup = $this->buildAliasLookup(self::COLLECTION_HEADER_ALIASES);
        $forcedPeriodKey = $forcedPeriodKey !== null
            ? $this->normalizer->parseMonthKey($forcedPeriodKey)
            : null;

        $headerMap = [];
        $errors = [];
        $normalizedRows = [];
        $periods = [];
        $seenKeys = [];
        $totalRows = 0;
        $emptyRows = 0;
        $duplicateRows = 0;
        $headerRead = false;
        $periodResolution = [
            'mode' => null,
            'label' => null,
            'source' => null,
            'period_key' => null,
        ];

        foreach ($this->spreadsheetReader->rows($path) as $row) {
            $rowNumber = $row['row_number'];
            $values = $row['values'];

            if (! $headerRead) {
                $headerRead = true;

                if ($this->normalizer->isEmptyRow($values)) {
                    $errors[] = LoadValidationErrorData::general('El archivo de cartera esta vacio o no contiene un encabezado util.', 'empty_file');
                    break;
                }

                $headerMap = $this->mapHeaders($values, $aliasLookup);
                $collectionHeaderMap = $this->mapHeaders($values, $collectionLookup);

                if (count($collectionHeaderMap) >= 2 && count($headerMap) < count($collectionHeaderMap)) {
                    $errors[] = LoadValidationErrorData::general(
                        'El archivo parece corresponder al modulo de recaudos. Use la carga de recaudos para este archivo.',
                        'wrong_module'
                    );

                    break;
                }

                if (count($headerMap) < 4) {
                    $errors[] = LoadValidationErrorData::general(
                        'No fue posible reconocer la estructura de cartera. Revise la plantilla y el encabezado del archivo.',
                        'invalid_header'
                    );

                    break;
                }

                $missingHeaders = array_values(array_diff(self::REQUIRED_HEADERS, array_keys($headerMap)));

                if ($missingHeaders !== []) {
                    $errors[] = LoadValidationErrorData::general(
                        'Faltan columnas obligatorias: ' . implode(', ', $missingHeaders) . '.',
                        'missing_required_headers',
                        ['missing_headers' => $missingHeaders],
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

            $rowErrors = [];
            $payload = $this->rowPayload($headerMap, $values);
            $normalized = $this->normalizeRow($rowNumber, $payload, $rowErrors);

            if ($normalized !== null) {
                if (isset($seenKeys[$normalized['logical_key']])) {
                    $duplicateRows++;
                    $rowErrors[] = new LoadValidationErrorData(
                        rowNumber: $rowNumber,
                        field: 'nro_documento',
                        code: 'duplicate_row',
                        message: 'El documento esta duplicado dentro del mismo archivo.',
                        payload: ['logical_key' => $normalized['duplicate_signature']],
                    );
                } else {
                    $seenKeys[$normalized['logical_key']] = true;
                }
            }

            if ($rowErrors !== []) {
                array_push($errors, ...$rowErrors);
                continue;
            }

            $periods[$normalized['period_key']] = ($periods[$normalized['period_key']] ?? 0) + 1;
            $normalizedRows[] = $normalized;
        }

        if (! $headerRead) {
            $errors[] = LoadValidationErrorData::general('El archivo de cartera no contiene filas para procesar.', 'empty_file');
        }

        $periodKey = null;
        $periodDate = null;

        if ($normalizedRows !== [] && $errors === []) {
            if ($forcedPeriodKey !== null) {
                $periodKey = $forcedPeriodKey;
                $periodDate = $this->normalizer->firstDayOfPeriod($periodKey);
                $periodResolution = [
                    'mode' => 'manual',
                    'label' => 'Periodo informado manualmente por el usuario.',
                    'source' => 'upload_form',
                    'period_key' => $periodKey,
                ];

                $normalizedRows = array_map(
                    static function (array $row) use ($periodKey, $periodDate): array {
                        $row['period_key'] = $periodKey;
                        $row['period_date'] = $periodDate->toDateString();

                        return $row;
                    },
                    $normalizedRows,
                );

                try {
                    $this->periodControlService->assertPortfolioChronology($periodDate);
                } catch (DomainException $exception) {
                    $errors[] = LoadValidationErrorData::general($exception->getMessage(), 'chronology_blocked');
                }
            } elseif (count($periods) !== 1) {
                $inferredPeriodKey = $sourceName !== null
                    ? $this->normalizer->inferPortfolioPeriodFromFilename($sourceName, array_keys($periods))
                    : null;

                if ($inferredPeriodKey !== null) {
                    $periodKey = $inferredPeriodKey;
                    $periodDate = $this->normalizer->firstDayOfPeriod($periodKey);
                    $periodResolution = [
                        'mode' => 'filename_inference',
                        'label' => 'Periodo inferido automaticamente desde el nombre del archivo.',
                        'source' => $sourceName,
                        'period_key' => $periodKey,
                    ];

                    $normalizedRows = array_map(
                        static function (array $row) use ($periodKey, $periodDate): array {
                            $row['period_key'] = $periodKey;
                            $row['period_date'] = $periodDate->toDateString();

                            return $row;
                        },
                        $normalizedRows,
                    );

                    try {
                        $this->periodControlService->assertPortfolioChronology($periodDate);
                    } catch (DomainException $exception) {
                        $errors[] = LoadValidationErrorData::general($exception->getMessage(), 'chronology_blocked');
                    }
                } else {
                    $periodKey = max(array_keys($periods));
                    $periodDate = $this->normalizer->firstDayOfPeriod($periodKey);
                    $periodResolution = [
                        'mode' => 'latest_period',
                        'label' => 'Periodo resuelto automaticamente usando el mas reciente del archivo (contiene documentos historicos de periodos anteriores).',
                        'source' => 'fecha_contabilizacion',
                        'period_key' => $periodKey,
                        'all_periods' => array_keys($periods),
                    ];

                    $normalizedRows = array_map(
                        static function (array $row) use ($periodKey, $periodDate): array {
                            $row['period_key'] = $periodKey;
                            $row['period_date'] = $periodDate->toDateString();

                            return $row;
                        },
                        $normalizedRows,
                    );

                    try {
                        $this->periodControlService->assertPortfolioChronology($periodDate);
                    } catch (DomainException $exception) {
                        $errors[] = LoadValidationErrorData::general($exception->getMessage(), 'chronology_blocked');
                    }
                }
            } else {
                $periodKey = array_key_first($periods);
                $periodDate = $this->normalizer->firstDayOfPeriod($periodKey);
                $periodResolution = [
                    'mode' => 'row_dates',
                    'label' => 'Periodo resuelto automaticamente desde las fechas del archivo.',
                    'source' => 'fecha_contabilizacion',
                    'period_key' => $periodKey,
                ];

                try {
                    $this->periodControlService->assertPortfolioChronology($periodDate);
                } catch (DomainException $exception) {
                    $errors[] = LoadValidationErrorData::general($exception->getMessage(), 'chronology_blocked');
                }
            }
        }

        if ($normalizedRows === [] && $errors === []) {
            $errors[] = LoadValidationErrorData::general(
                'No se encontraron filas validas para cartera despues de analizar el archivo.',
                'no_valid_rows'
            );
        }

        return new LoadValidationResultData(
            isValid: $errors === [] && $normalizedRows !== [],
            detectedModule: 'portfolio',
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
                    'Los buckets se recalculan internamente a partir de dias de mora y saldo pendiente.',
                    'El periodo debe ser unico y cronologicamente valido.',
                ],
                'forced_period_key' => $forcedPeriodKey,
                'period_resolution' => array_filter($periodResolution, static fn ($value) => $value !== null),
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
        foreach (self::REQUIRED_HEADERS as $requiredField) {
            if ($this->normalizer->normalizeText($payload[$requiredField] ?? null) === null) {
                $rowErrors[] = new LoadValidationErrorData(
                    rowNumber: $rowNumber,
                    field: $requiredField,
                    code: 'required_field',
                    message: "La columna {$requiredField} es obligatoria.",
                );
            }
        }

        foreach (self::MAX_LENGTHS as $field => $maxLength) {
            $value = $field === 'telefono'
                ? $this->normalizer->normalizePhone($payload[$field] ?? null)
                : $this->normalizer->normalizeText($payload[$field] ?? null);

            if ($value !== null && Str::length($value) > $maxLength) {
                $rowErrors[] = new LoadValidationErrorData(
                    rowNumber: $rowNumber,
                    field: $field,
                    code: 'max_length',
                    message: "La columna {$field} supera la longitud maxima permitida ({$maxLength}).",
                );
            }
        }

        $postingDate = $this->normalizer->parseDate($payload['fecha_contabilizacion'] ?? null);
        $activationDate = $this->normalizer->parseDate($payload['fecha_activacion'] ?? null);
        $dueDate = $this->normalizer->parseDate($payload['fecha_vencimiento'] ?? null);

        if (! $postingDate) {
            $rowErrors[] = new LoadValidationErrorData($rowNumber, 'fecha_contabilizacion', 'invalid_date', 'La fecha de contabilizacion no es valida.');
        }

        if (! $dueDate) {
            $rowErrors[] = new LoadValidationErrorData($rowNumber, 'fecha_vencimiento', 'invalid_date', 'La fecha de vencimiento no es valida.');
        }

        $originalAmount = $this->normalizer->parseNumber($payload['valor_documento'] ?? null);
        $pendingAmount = $this->normalizer->parseNumber($payload['saldo_pendiente'] ?? null);

        if ($originalAmount === null) {
            $rowErrors[] = new LoadValidationErrorData($rowNumber, 'valor_documento', 'invalid_number', 'El valor del documento debe ser numerico.');
        }

        if ($pendingAmount === null) {
            $rowErrors[] = new LoadValidationErrorData($rowNumber, 'saldo_pendiente', 'invalid_number', 'El saldo pendiente debe ser numerico.');
        }

        $daysOverdue = $this->normalizer->parseNumber($payload['dias_vencido'] ?? null);

        if ($daysOverdue !== null) {
            $daysOverdue = (int) round($daysOverdue);
        }

        if (
            $daysOverdue === null &&
            $postingDate instanceof CarbonImmutable &&
            $dueDate instanceof CarbonImmutable
        ) {
            $daysOverdue = $this->normalizer->calculateDaysOverdue($dueDate, $postingDate);
        }

        if ($daysOverdue === null) {
            $rowErrors[] = new LoadValidationErrorData($rowNumber, 'dias_vencido', 'missing_days', 'No fue posible resolver los dias de vencido a partir de la fecha de vencimiento.');
        }

        if ($rowErrors !== []) {
            return null;
        }

        $providedBuckets = $this->extractProvidedBuckets($payload);
        $providedBucketTotal = array_sum($providedBuckets);
        $calculatedBuckets = $this->calculateBuckets($pendingAmount, $daysOverdue);

        if ($providedBuckets !== [] && abs($providedBucketTotal - $pendingAmount) > 0.99) {
            $rowErrors[] = new LoadValidationErrorData(
                rowNumber: $rowNumber,
                field: 'saldo_pendiente',
                code: 'bucket_mismatch',
                message: 'La suma de los buckets reportados no coincide con el saldo pendiente.',
                payload: [
                    'provided_bucket_total' => $providedBucketTotal,
                    'pending_amount' => $pendingAmount,
                ],
            );

            return null;
        }

        $account = Str::upper($this->normalizer->normalizeText($payload['cuenta'] ?? null, 50) ?? '');
        $documentNumber = $this->normalizer->normalizeDocumentNumber($payload['nro_documento'] ?? null);
        $documentType = Str::upper($this->normalizer->normalizeText($payload['tipo'] ?? null, 50) ?? '');
        $clientIdentity = Str::upper(
            $this->normalizer->normalizeText($payload['nit'] ?? null, 30)
            ?? $this->normalizer->normalizeText($payload['cliente'] ?? null, 255)
            ?? ''
        );
        $clientReference = Str::upper($this->normalizer->normalizeText($payload['nro_ref_de_cliente'] ?? null, 100) ?? '');
        $periodKey = $postingDate->format('Y-m');
        $periodDate = $this->normalizer->firstDayOfPeriod($periodKey)->toDateString();
        $duplicateSignature = implode('|', [$account, $clientIdentity, $documentNumber, $documentType, $clientReference, (string) $pendingAmount]);

        return [
            'row_number' => $rowNumber,
            'account' => $account,
            'client_name' => $this->normalizer->normalizeText($payload['cliente'] ?? null, 255),
            'nit' => $this->normalizer->normalizeText($payload['nit'] ?? null, 30),
            'address' => $this->normalizer->normalizeText($payload['direccion'] ?? null, 255),
            'contact' => $this->normalizer->normalizeText($payload['contacto'] ?? null, 120),
            'phone' => $this->normalizer->normalizePhone($payload['telefono'] ?? null, 30),
            'channel' => $this->normalizer->normalizeText($payload['canal'] ?? null, 100),
            'uen' => $this->normalizer->normalizeText($payload['uens'] ?? null, 100),
            'sales_employee' => $this->normalizer->normalizeText($payload['empleado_de_ventas'] ?? null, 120),
            'regional' => $this->normalizer->normalizeText($payload['regional'] ?? null, 100),
            'document_number' => $documentNumber,
            'client_reference' => $this->normalizer->normalizeText($payload['nro_ref_de_cliente'] ?? null, 100),
            'document_type' => $documentType,
            'issue_date' => $postingDate->toDateString(),
            'activation_date' => $activationDate?->toDateString(),
            'due_date' => $dueDate?->toDateString(),
            'original_amount' => (float) $originalAmount,
            'pending_amount' => (float) $pendingAmount,
            'currency' => Str::upper($this->normalizer->normalizeText($payload['moneda'] ?? 'COP', 10) ?? 'COP'),
            'days_overdue' => $daysOverdue,
            'aging_buckets' => $calculatedBuckets,
            'period_key' => $periodKey,
            'period_date' => $periodDate,
            'logical_key' => sha1($duplicateSignature),
            'duplicate_signature' => $duplicateSignature,
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

    private function extractProvidedBuckets(array $payload): array
    {
        $buckets = [];

        foreach ([
            'actual',
            '1_30_dias',
            '31_60_dias',
            '61_90_dias',
            '91_180_dias',
            '181_360_dias',
            '361_dias',
        ] as $field) {
            $number = $this->normalizer->parseNumber($payload[$field] ?? null);

            if ($number !== null) {
                $buckets[$field] = (float) $number;
            }
        }

        return $buckets;
    }

    private function calculateBuckets(float $pendingAmount, int $daysOverdue): array
    {
        $buckets = [
            'actual' => 0.0,
            '1_30_dias' => 0.0,
            '31_60_dias' => 0.0,
            '61_90_dias' => 0.0,
            '91_180_dias' => 0.0,
            '181_360_dias' => 0.0,
            '361_dias' => 0.0,
        ];

        $target = match (true) {
            $pendingAmount <= 0 => 'actual',
            $daysOverdue <= 0 => 'actual',
            $daysOverdue <= 30 => '1_30_dias',
            $daysOverdue <= 60 => '31_60_dias',
            $daysOverdue <= 90 => '61_90_dias',
            $daysOverdue <= 180 => '91_180_dias',
            $daysOverdue <= 360 => '181_360_dias',
            default => '361_dias',
        };

        $buckets[$target] = round($pendingAmount, 2);

        return $buckets;
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
