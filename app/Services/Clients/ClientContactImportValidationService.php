<?php

namespace App\Services\Clients;

use App\Data\Loads\LoadValidationErrorData;
use App\Services\Loads\Support\ImportNormalizer;
use App\Services\Loads\Support\SpreadsheetReader;

class ClientContactImportValidationService
{
    private const HEADER_ALIASES = [
        'nit' => ['nit', 'documento', 'document_number', 'numero_documento', 'identificacion', 'tax_id'],
        'code' => ['codigo', 'code', 'cuenta', 'cod_cliente', 'cliente_codigo'],
        'name' => [
            'nombre', 'cliente', 'razon_social', 'nombre_cliente', 'name', 'nombre_sn',
        ],
        'email' => ['email', 'correo', 'correo_electronico', 'e_mail'],
        'phone' => ['telefono', 'teléfono', 'phone', 'celular', 'movil', 'telefono_movil'],
        'address' => ['direccion', 'dirección', 'address', 'domicilio', 'direccion_mm'],
        'city' => ['ciudad', 'city', 'municipio', 'ciudad_mm'],
        'region' => ['regional', 'region', 'zona', 'territorio'],
        'channel' => ['canal', 'channel', 'desc_canal', 'grupo'],
        'uen' => ['uen', 'unidad_de_negocio', 'business_unit'],
        'credit_limit' => ['limite_de_credito', 'limite_credito', 'credit_limit'],
        'contact_name' => ['contacto', 'contact_name', 'nombre_contacto', 'contacto_responsable'],
        'contact_email' => ['email_contacto', 'correo_contacto', 'contact_email'],
        'contact_phone' => ['telefono_contacto', 'teléfono_contacto', 'contact_phone', 'celular_contacto'],
        'fecha_creacion' => [
            'fecha_creacion', 'fecha_de_creacion', 'fechacreacion', 'fecha_creacion_cliente',
            'fecha_alta', 'fecha_registro', 'fecha_creacion_sap', 'created_date',
        ],
    ];

    private const MAX_STORED_ERRORS = 200;

    public function __construct(
        private readonly SpreadsheetReader $spreadsheetReader,
        private readonly ImportNormalizer $normalizer,
    ) {}

    /**
     * @return array{
     *     is_valid: bool,
     *     rows: array<int, array<string, mixed>>,
     *     errors: array<int, LoadValidationErrorData>,
     *     total_rows: int,
     *     error_rows: int,
     *     skipped_rows: int,
     * }
     */
    public function validate(string $path): array
    {
        $lookup = $this->buildAliasLookup();
        $errors = [];
        $normalizedRows = [];
        $totalRows = 0;
        $skippedRows = 0;
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
                        'No se encontró la fila de encabezados con columna NIT.',
                        'missing_headers',
                    );
                    break;
                }

                continue;
            }

            if ($this->normalizer->isEmptyRow($values)) {
                $skippedRows++;
                continue;
            }

            $totalRows++;
            $payload = $this->rowPayload($headerMap, $values);

            $documentNumber = $this->normalizer->normalizeDocumentNumber($payload['nit'] ?? null);
            $code = $this->normalizer->normalizeText($payload['code'] ?? null, 50);

            if ($documentNumber === null) {
                $errors[] = new LoadValidationErrorData(
                    $rowNumber,
                    'nit',
                    'required',
                    'Indica el NIT para identificar el cliente.',
                );
                continue;
            }

            $contactFields = $this->extractContactFields($payload);
            $masterCreatedAt = $this->parseMasterCreatedAt($payload);

            if ($contactFields === [] && $masterCreatedAt === null) {
                $skippedRows++;
                continue;
            }

            $normalizedRows[] = array_merge([
                'row_number' => $rowNumber,
                'document_number' => $documentNumber,
                'code' => $code,
            ], $contactFields, $masterCreatedAt !== null ? ['master_created_at' => $masterCreatedAt->toDateString()] : []);
        }

        if ($headerMap === null && $errors === []) {
            $errors[] = LoadValidationErrorData::general(
                'No se encontró la fila de encabezados del archivo maestro.',
                'missing_headers',
            );
        }

        if ($normalizedRows === [] && $errors === []) {
            $errors[] = LoadValidationErrorData::general(
                'El archivo no contiene filas útiles para actualizar contactos.',
                'empty_file',
            );
        }

        $errorRows = count($errors);
        $isValid = $normalizedRows !== [] && $errorRows === 0;

        return [
            'is_valid' => $isValid,
            'rows' => $normalizedRows,
            'errors' => array_slice($errors, 0, self::MAX_STORED_ERRORS),
            'total_rows' => $totalRows,
            'error_rows' => $errorRows,
            'skipped_rows' => $skippedRows,
        ];
    }

    /**
     * @param  array<string, int>  $map
     */
    private function hasRequiredHeaders(array $map): bool
    {
        return isset($map['nit']);
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

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, string>
     */
    private function extractContactFields(array $payload): array
    {
        $fields = [];

        $map = [
            'name' => fn (mixed $v) => $this->normalizer->normalizeText($v, 255),
            'email' => fn (mixed $v) => $this->normalizer->normalizeText($v, 255),
            'phone' => fn (mixed $v) => $this->normalizer->normalizePhone($v, 30),
            'address' => fn (mixed $v) => $this->normalizer->normalizeText($v, 255),
            'city' => fn (mixed $v) => $this->normalizer->normalizeText($v, 100),
            'region' => fn (mixed $v) => $this->normalizer->normalizeText($v, 100),
            'channel' => fn (mixed $v) => $this->normalizer->normalizeText($v, 100),
            'uen' => fn (mixed $v) => $this->normalizer->normalizeText($v, 100),
            'contact_name' => fn (mixed $v) => $this->normalizer->normalizeText($v, 255),
            'contact_email' => fn (mixed $v) => $this->normalizer->normalizeText($v, 255),
            'contact_phone' => fn (mixed $v) => $this->normalizer->normalizePhone($v, 30),
            'credit_limit' => fn (mixed $v) => $this->normalizeCreditLimit($v),
        ];

        foreach ($map as $field => $normalizer) {
            $value = $normalizer($payload[$field] ?? null);

            if ($value !== null) {
                $fields[$field] = $value;
            }
        }

        return $fields;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function parseMasterCreatedAt(array $payload): ?\Carbon\CarbonImmutable
    {
        return $this->normalizer->parseDate($payload['fecha_creacion'] ?? null);
    }

    private function normalizeCreditLimit(mixed $value): ?float
    {
        $text = trim((string) ($value ?? ''));

        if ($text === '' || $text === '-') {
            return null;
        }

        return $this->normalizer->parseNumber($value);
    }
}
