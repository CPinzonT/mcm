<?php

namespace App\Services;

use App\Models\Advisor;
use App\Models\Client;
use App\Models\PortfolioDocument;
use App\Models\PortfolioLoad;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use OpenSpout\Reader\XLSX\Reader;

class PortfolioImportService
{
    const TYPE_MAP = [
        'SI'     => 'Saldo Inicial',
        'FVNal1' => 'Factura',
        'FVNal2' => 'Factura',
        'FVNal3' => 'Factura',
        'FVExp1' => 'Factura',
        'FVExp2' => 'Factura',
        'NDNal'  => 'Nota Débito',
        'NDExp'  => 'Nota Débito',
        'NCNal'  => 'Nota Crédito',
        'NCExp'  => 'Nota Crédito',
        'AC'     => 'Asientos Contables',
        'RC'     => 'Recibo de Caja',
    ];

    const HEADER_MAP = [
        'cuenta'                => 'account',
        'cliente'               => 'client_name',
        'nit'                   => 'nit',
        'dirección'             => 'address',
        'direccion'             => 'address',
        'contacto'              => 'contact',
        'teléfono'              => 'phone',
        'telefono'              => 'phone',
        'canal'                 => 'channel',
        'empleado de ventas'    => 'advisor',
        'regional'              => 'regional',
        'nro. documento'        => 'document_number',
        'nro. ref. de cliente'  => 'ref_client',
        'tipo'                  => 'document_type',
        'fecha contabilización' => 'issue_date',
        'fecha contabilizacion' => 'issue_date',
        'fecha vencimiento'     => 'due_date',
        'valor documento'       => 'original_amount',
        'saldo pendiente'       => 'pending_amount',
        'moneda'                => 'currency',
        'dias vencido'          => 'days_overdue',
        'días vencido'          => 'days_overdue',
    ];

    private const CHUNK_SIZE = 500;

    public function process(PortfolioLoad $load): void
    {
        $load->update(['status' => 'processing']);

        $errors       = [];
        $processed    = 0;
        $errCount     = 0;
        $total        = 0;
        $headers      = [];
        $chunk        = [];
        $clientCache  = [];
        $advisorCache = [];
        $rowNumber    = 0;

        try {
            $path = Storage::disk($load->disk)->path($load->path);

            $reader = new Reader();
            $reader->open($path);

            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $row) {
                    $cells = $row->toArray();
                    $rowNumber++;

                    if ($rowNumber === 1) {
                        $headers = $this->parseHeaders($cells);
                        continue;
                    }

                    if (empty($headers)) {
                        continue;
                    }

                    $total++;
                    $chunk[] = ['values' => $cells, 'row' => $rowNumber];

                    if (\count($chunk) >= self::CHUNK_SIZE) {
                        [$p, $e, $err] = $this->processChunk($chunk, $headers, $load, $clientCache, $advisorCache);
                        $processed += $p;
                        $errCount  += $e;
                        $errors     = array_merge($errors, $err);
                        $chunk      = [];
                    }
                }
                break; // solo primera hoja
            }

            if (!empty($chunk)) {
                [$p, $e, $err] = $this->processChunk($chunk, $headers, $load, $clientCache, $advisorCache);
                $processed += $p;
                $errCount  += $e;
                $errors     = array_merge($errors, $err);
            }

            $reader->close();

            $load->update([
                'status'         => 'completed',
                'total_rows'     => $total,
                'processed_rows' => $processed,
                'error_rows'     => $errCount,
                'error_log'      => $errors ?: null,
                'processed_at'   => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error("PortfolioImport failed: " . $e->getMessage());
            $load->update(['status' => 'failed', 'error_log' => [['error' => $e->getMessage()]]]);
            throw $e;
        }
    }

    private function processChunk(array $chunk, array $headers, PortfolioLoad $load, array &$clientCache, array &$advisorCache): array
    {
        $processed = 0;
        $errCount  = 0;
        $errors    = [];

        DB::beginTransaction();
        try {
            foreach ($chunk as ['values' => $cells, 'row' => $rowNumber]) {
                $data = $this->mapRow($cells, $headers);

                if (empty($data['client_name']) && empty($data['nit'])) {
                    continue;
                }

                try {
                    $client  = $this->resolveClient($data, $clientCache);
                    $advisor = $this->resolveAdvisor($data['advisor'] ?? null, $advisorCache);

                    $daysOverdue = (int) ($data['days_overdue'] ?? 0);
                    $riskLevel   = $this->calculateRisk($daysOverdue);
                    $docType     = self::TYPE_MAP[trim($data['document_type'] ?? '')] ?? ($data['document_type'] ?? 'Factura');
                    $pending     = (float) ($data['pending_amount'] ?? 0);

                    PortfolioDocument::updateOrCreate(
                        [
                            'client_id'       => $client->id,
                            'document_number' => (string) ($data['document_number'] ?? ''),
                            'period_date'     => $load->period_date,
                        ],
                        [
                            'portfolio_load_id' => $load->id,
                            'advisor_id'        => $advisor?->id,
                            'document_type'     => $docType,
                            'issue_date'        => $this->parseDate($data['issue_date'] ?? null),
                            'due_date'          => $this->parseDate($data['due_date'] ?? null),
                            'original_amount'   => (float) ($data['original_amount'] ?? 0),
                            'pending_amount'    => $pending,
                            'days_overdue'      => $daysOverdue,
                            'risk_level'        => $riskLevel,
                            'currency'          => $data['currency'] ?? 'COP',
                            'status'            => $pending <= 0 ? 'paid' : 'active',
                        ]
                    );

                    $processed++;
                } catch (\Throwable $e) {
                    $errCount++;
                    $errors[] = ['row' => $rowNumber, 'error' => $e->getMessage()];
                    Log::warning("PortfolioImport row {$rowNumber}: " . $e->getMessage());
                }
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $errCount += \count($chunk);
            $errors[]  = ['chunk_error' => $e->getMessage()];
        }

        return [$processed, $errCount, $errors];
    }

    private function parseHeaders(array $headerRow): array
    {
        $map = [];
        foreach ($headerRow as $colIndex => $header) {
            $normalized = mb_strtolower(trim((string) $header));
            if (isset(self::HEADER_MAP[$normalized])) {
                $map[self::HEADER_MAP[$normalized]] = $colIndex;
            }
        }
        return $map;
    }

    private function mapRow(array $row, array $headers): array
    {
        $data = [];
        foreach ($headers as $field => $colIndex) {
            $data[$field] = $row[$colIndex] ?? null;
        }
        return $data;
    }

    private function resolveClient(array $data, array &$cache): Client
    {
        $nit  = trim((string) ($data['nit'] ?? ''));
        $name = trim((string) ($data['client_name'] ?? ''));
        $key  = $nit ?: $name;

        if (isset($cache[$key])) {
            return $cache[$key];
        }

        $docNumber = $nit ?: ('IMP-' . substr(md5($name), 0, 10));
        $phone     = $data['phone'] ?? null;
        if ($phone) {
            $phone = substr(trim(explode('-', (string) $phone)[0]), 0, 30);
        }

        $cache[$key] = Client::firstOrCreate(
            ['document_number' => $docNumber],
            [
                'code'          => $docNumber,
                'name'          => $name,
                'document_type' => 'NIT',
                'phone'         => $phone,
                'address'       => $data['address'] ?? null,
                'region'        => $data['regional'] ?? null,
                'channel'       => $data['channel'] ?? null,
                'contact_name'  => $data['contact'] ?? null,
                'active'        => true,
            ]
        );

        return $cache[$key];
    }

    private function resolveAdvisor(?string $name, array &$cache): ?Advisor
    {
        if (empty(trim((string) $name))) {
            return null;
        }
        $name = trim($name);
        if (!isset($cache[$name])) {
            $cache[$name] = Advisor::firstOrCreate(
                ['name' => $name],
                ['code' => 'IMP-' . substr(md5($name), 0, 8), 'active' => true]
            );
        }
        return $cache[$name];
    }

    private function calculateRisk(int $days): string
    {
        return match(true) {
            $days <= 30  => 'normal',
            $days <= 60  => 'low',
            $days <= 90  => 'medium',
            $days <= 180 => 'high',
            default      => 'critical',
        };
    }

    private function parseDate(mixed $value): ?string
    {
        if (empty($value)) {
            return null;
        }
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }
        if (is_numeric($value)) {
            return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-d');
        }
        foreach (['d/m/Y', 'Y-m-d', 'd-m-Y'] as $fmt) {
            $d = \DateTime::createFromFormat($fmt, trim((string) $value));
            if ($d) return $d->format('Y-m-d');
        }
        return null;
    }
}
