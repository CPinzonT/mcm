<?php

namespace App\Services\Budget;

use App\Models\BudgetLoad;
use App\Models\BudgetRow;
use App\Models\User;
use App\Services\Loads\Support\ImportNormalizer;
use App\Services\Loads\Support\SpreadsheetReader;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BudgetImportService
{
    private const HEADER_ALIASES = [
        'nombre_cliente'    => ['nombre_cliente', 'nombre_cliente_', 'cliente', 'nombre_del_cliente', 'razon_social'],
        'regional'          => ['regional'],
        'desc_canal'        => ['desc_canal', 'descripcion_canal', 'canal', 'desc_canal_venta'],
        'vendedor'          => ['vendedor', 'asesor', 'nombre_vendedor', 'seller'],
        'tipo_transac'      => ['tipo_transac', 'tipo_transaccion', 'tipo_trans', 'tipo'],
        'no_factura'        => ['no_factura', 'nro_factura', 'numero_factura', 'factura', 'documento'],
        'fecha_factura'     => ['fecha_factura', 'fecha_de_factura'],
        'fecha_vencimiento' => ['fecha_vencimiento', 'fecha_de_vencimiento', 'vencimiento'],
        'dias_vencimiento'  => ['dias_vencimi', 'dias_vencimiento', 'dias_vencido'],
        'monto_inicial'     => ['monto_inicia', 'monto_inicial', 'importe_inicial'],
        'saldo_debido'      => ['saldo_debido', 'saldo', 'balance'],
        'aging_1_90'        => ['1_90', '1-90', 'aging_1_90', '90_1'],
        'aging_over_90'     => ['over_90', 'aging_over_90', 'mas_90', 'mayor_90'],
        'sin_vencer'        => ['sin_vencer', 'no_vencido'],
        'rotacion'          => ['rotacion', 'rotation'],
        'ppto'              => ['ppto', 'presupuesto', 'meta', 'budget'],
        'recaudo'           => ['recaudo', 'collection', 'cobro'],
        'categoria'         => ['categorias', 'categoria', 'category'],
        'fecha_aplicacion'  => ['fecha_de_aplicacion', 'fecha_aplicacion', 'fecha_aplicacion_pago'],
    ];

    public function __construct(
        private readonly SpreadsheetReader $spreadsheetReader,
        private readonly ImportNormalizer $normalizer,
    ) {}

    /**
     * @return array{load: BudgetLoad, errors: string[]}
     */
    public function handleUpload(UploadedFile $file, ?string $notes, User $user, ?string $forcedPeriodKey = null): array
    {
        $extension = strtolower($file->getClientOriginalExtension());
        if (! in_array($extension, ['csv', 'xlsx', 'xls'], true)) {
            throw new \DomainException('Solo se permiten archivos CSV o Excel (.xlsx, .xls).');
        }

        $path = $file->store('budget-loads', 'local');
        $absolutePath = Storage::disk('local')->path($path);

        $lookup = $this->buildAliasLookup();
        $errors = [];
        $rows = [];
        $headerMap = null;
        $periodKeys = [];

        foreach ($this->spreadsheetReader->rows($absolutePath) as $row) {
            $values = $row['values'];

            if ($headerMap === null) {
                if ($this->normalizer->isEmptyRow($values)) {
                    continue;
                }

                $candidate = $this->mapHeaders($values, $lookup);

                if ($this->isBudgetHeaderRow($candidate)) {
                    $headerMap = $candidate;
                    continue;
                }

                if ($row['row_number'] > 30) {
                    throw new \DomainException($this->unrecognizedHeaderMessage($candidate));
                }

                continue;
            }

            if ($this->normalizer->isEmptyRow($values)) {
                continue;
            }

            $payload = $this->rowPayload($headerMap, $values);
            $applicationDate = $this->normalizer->parseDate($payload['fecha_aplicacion'] ?? null);
            $invoiceDate = $this->normalizer->parseDate($payload['fecha_factura'] ?? null);
            $dueDate = $this->normalizer->parseDate($payload['fecha_vencimiento'] ?? null);

            $periodKey = $forcedPeriodKey
                ?: ($applicationDate?->format('Y-m')
                    ?? $invoiceDate?->format('Y-m')
                    ?? $dueDate?->format('Y-m'));

            $ppto = $this->normalizer->parseNumber($payload['ppto'] ?? null);
            $recaudo = $this->normalizer->parseNumber($payload['recaudo'] ?? null);
            $balanceDue = $this->normalizer->parseNumber($payload['saldo_debido'] ?? null);
            $initialAmount = $this->normalizer->parseNumber($payload['monto_inicial'] ?? null);

            $clientName = $this->normalizer->normalizeText($payload['nombre_cliente'] ?? null, 255);
            $documentNumber = $this->normalizer->normalizeDocumentNumber($payload['no_factura'] ?? null);

            $hasMoney = ($ppto !== null && $ppto != 0)
                || ($recaudo !== null && $recaudo != 0)
                || ($balanceDue !== null && $balanceDue != 0)
                || ($initialAmount !== null && $initialAmount != 0);

            $hasIdentity = $clientName !== null || $documentNumber !== null;

            if (! $hasMoney && ! $hasIdentity) {
                $errors[] = "Fila {$row['row_number']}: sin datos útiles (montos o cliente/documento).";
                continue;
            }

            if ($periodKey === null) {
                if ($forcedPeriodKey) {
                    $periodKey = $forcedPeriodKey;
                } else {
                    $errors[] = "Fila {$row['row_number']}: sin fecha de aplicación, factura o vencimiento para el período.";
                    continue;
                }
            }

            $periodKeys[$periodKey] = true;
            $rows[] = [
                'period_key'         => $periodKey,
                'row_number'         => $row['row_number'],
                'client_name'        => $clientName,
                'regional'           => $this->normalizer->normalizeText($payload['regional'] ?? null, 80),
                'channel'            => $this->normalizer->normalizeText($payload['desc_canal'] ?? null, 120),
                'seller_name'        => $this->normalizer->normalizeText($payload['vendedor'] ?? null, 150),
                'transaction_type'   => $this->normalizer->normalizeText($payload['tipo_transac'] ?? null, 80),
                'document_number'    => $documentNumber,
                'invoice_date'       => $invoiceDate?->toDateString(),
                'due_date'           => $dueDate?->toDateString(),
                'days_overdue'       => $this->parseInt($payload['dias_vencimiento'] ?? null),
                'initial_amount'     => $initialAmount,
                'balance_due'        => $balanceDue,
                'aging_1_90'         => $this->normalizer->parseNumber($payload['aging_1_90'] ?? null),
                'aging_over_90'      => $this->normalizer->parseNumber($payload['aging_over_90'] ?? null),
                'not_due_amount'     => $this->normalizer->parseNumber($payload['sin_vencer'] ?? null),
                'rotation'           => $this->normalizer->parseNumber($payload['rotacion'] ?? null),
                'budget_amount'      => $ppto,
                'collection_amount'  => $recaudo,
                'category'           => $this->normalizer->normalizeText($payload['categoria'] ?? null, 150),
                'application_date'   => $applicationDate?->toDateString(),
            ];
        }

        if ($headerMap === null) {
            throw new \DomainException(
                'No se encontró la fila de encabezados del presupuesto. Verifica que existan columnas PPTO, RECAUDO o el bloque Cliente/Regional/Canal.'
            );
        }

        if ($rows === [] && $errors !== []) {
            throw new \DomainException(implode("\n", array_slice($errors, 0, 8)));
        }

        if ($rows === []) {
            throw new \DomainException('No se encontraron filas válidas de presupuesto.');
        }

        $periodKey = $forcedPeriodKey ?: (count($periodKeys) === 1 ? array_key_first($periodKeys) : null);
        $totalPpto = array_sum(array_map(static fn ($r) => (float) ($r['budget_amount'] ?? 0), $rows));
        $totalRecaudo = array_sum(array_map(static fn ($r) => (float) ($r['collection_amount'] ?? 0), $rows));

        $load = BudgetLoad::query()->create([
            'reference'         => 'BG-' . Str::upper(Str::random(8)),
            'original_filename' => $file->getClientOriginalName(),
            'disk'              => 'local',
            'path'              => $path,
            'period_key'        => $periodKey,
            'total_rows'        => count($rows) + count($errors),
            'valid_rows'        => count($rows),
            'error_rows'        => count($errors),
            'total_amount'      => $totalPpto,
            'status'            => 'completed',
            'notes'             => $notes,
            'error_log'         => $errors !== [] ? ['messages' => array_slice($errors, 0, 100)] : null,
            'uploaded_by'       => $user->id,
            'processed_at'      => now(),
        ]);

        DB::transaction(function () use ($rows, $load, $periodKeys): void {
            foreach (array_keys($periodKeys) as $pk) {
                BudgetRow::query()->where('period_key', $pk)->delete();
            }

            $now = now();
            foreach (array_chunk($rows, 500) as $chunk) {
                $insert = [];
                foreach ($chunk as $r) {
                    $insert[] = array_merge($r, [
                        'budget_load_id' => $load->id,
                        'created_at'     => $now,
                        'updated_at'     => $now,
                    ]);
                }
                BudgetRow::query()->insert($insert);
            }
        });

        return ['load' => $load->fresh(), 'errors' => $errors, 'total_recaudo' => $totalRecaudo];
    }

    private function parseInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }
        $n = $this->normalizer->parseNumber($value);

        return $n !== null ? (int) round($n) : null;
    }

    /**
     * @param  array<string, int>  $map
     */
    private function isBudgetHeaderRow(array $map): bool
    {
        if (isset($map['ppto']) || isset($map['recaudo'])) {
            return true;
        }

        return count($map) >= 5
            && (isset($map['nombre_cliente']) || isset($map['no_factura']) || isset($map['tipo_transac']));
    }

    /**
     * @param  array<string, int>  $partial
     */
    private function unrecognizedHeaderMessage(array $partial): string
    {
        $found = $partial === [] ? 'ninguna' : implode(', ', array_keys($partial));

        return "No se reconocieron los encabezados del presupuesto. Columnas detectadas: {$found}. "
            . 'Se esperan, entre otras: NOMBRE CLIENTE, REGIONAL, DESC CANAL, VENDEDOR, TIPO TRANSACCION, PPTO, RECAUDO.';
    }

    /**
     * @param  array<string, string>  $lookup
     * @return array<string, int>
     */
    private function mapHeaders(array $values, array $lookup): array
    {
        $map = [];

        foreach ($values as $index => $header) {
            $raw = trim((string) $header);

            if ($raw === '' || preg_match('/^column\d+$/i', $raw)) {
                continue;
            }

            if ($this->isOver90Header($raw)) {
                $map['aging_over_90'] ??= $index;
                continue;
            }

            if ($this->isOneToNinetyHeader($raw)) {
                $map['aging_1_90'] ??= $index;
                continue;
            }

            $key = $this->normalizer->normalizeHeader($raw);

            if ($key === '' || ! isset($lookup[$key])) {
                continue;
            }

            $canonical = $lookup[$key];
            $map[$canonical] ??= $index;
        }

        return $map;
    }

    private function isOver90Header(string $raw): bool
    {
        return (bool) preg_match('/^>\s*90$/i', $raw);
    }

    private function isOneToNinetyHeader(string $raw): bool
    {
        return (bool) preg_match('/^1\s*[-–]\s*90$/i', $raw);
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
            $lookup[$this->normalizer->normalizeHeader($canonical)] = $canonical;
        }

        return $lookup;
    }

    /**
     * @param  array<string, int>  $headerMap
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
