<?php

namespace App\Services;

use App\Models\Client;
use App\Models\CollectionDetail;
use App\Models\CollectionLoad;
use App\Models\PortfolioDocument;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use OpenSpout\Reader\XLSX\Reader;

class CollectionImportService
{
    const TYPE_MAP = [
        'Factura'      => 'Factura',
        'Nota Débito'  => 'Nota Débito',
        'Nota Debito'  => 'Nota Débito',
        'Nota Crédito' => 'Nota Crédito',
        'Nota Credito' => 'Nota Crédito',
        'RC'           => 'Recibo de Caja',
        'SI'           => 'Saldo Inicial',
        'AC'           => 'Asientos Contables',
    ];

    const HEADER_MAP = [
        'nro. de recibo'          => 'receipt_number',
        'fecha de recibo'         => 'payment_date',
        'total pago recibido'     => 'total_payment',
        'saldo'                   => 'balance',
        'id reconciliación'       => 'reconciliation_id',
        'id reconciliacion'       => 'reconciliation_id',
        'cliente'                 => 'client_name',
        'vendedor'                => 'advisor',
        'tipo documento aplicado' => 'document_type',
        'nro. documento aplicado' => 'document_number',
        'fecha de vencimiento'    => 'due_date',
        'fecha de aplicación'     => 'application_date',
        'fecha de aplicacion'     => 'application_date',
        'total documento'         => 'total_document',
        'importe aplicado'        => 'applied_amount',
        'saldo pendiente'         => 'pending_amount_after',
        'grupo'                   => 'channel',
        'regional'                => 'regional',
    ];

    private const CHUNK_SIZE = 300;

    public function process(CollectionLoad $load): void
    {
        $load->update(['status' => 'processing']);

        $errors         = [];
        $processed      = 0;
        $errCount       = 0;
        $totalCollected = 0.0;
        $total          = 0;
        $clientCache    = [];
        $headers        = [];
        $chunk          = [];
        $rowNumber      = 0;

        try {
            $path   = Storage::disk($load->disk)->path($load->path);
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
                        [$p, $e, $t, $err] = $this->processChunk($chunk, $headers, $load, $clientCache);
                        $processed      += $p;
                        $errCount       += $e;
                        $totalCollected += $t;
                        $errors          = array_merge($errors, $err);
                        $chunk           = [];
                        gc_collect_cycles();
                    }
                }
                break; // solo primera hoja
            }

            if (!empty($chunk)) {
                [$p, $e, $t, $err] = $this->processChunk($chunk, $headers, $load, $clientCache);
                $processed      += $p;
                $errCount       += $e;
                $totalCollected += $t;
                $errors          = array_merge($errors, $err);
            }

            $reader->close();

            $load->update([
                'status'          => 'completed',
                'total_rows'      => $total,
                'processed_rows'  => $processed,
                'error_rows'      => $errCount,
                'total_collected' => $totalCollected,
                'error_log'       => $errors ?: null,
                'processed_at'    => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error("CollectionImport failed: " . $e->getMessage());
            $load->update(['status' => 'failed', 'error_log' => [['error' => $e->getMessage()]]]);
            throw $e;
        }
    }

    private function processChunk(array $chunk, array $headers, CollectionLoad $load, array &$clientCache): array
    {
        $processed      = 0;
        $errCount       = 0;
        $totalCollected = 0.0;
        $errors         = [];

        DB::beginTransaction();
        try {
            foreach ($chunk as ['values' => $cells, 'row' => $rowNumber]) {
                $data = $this->mapRow($cells, $headers);

                if (empty($data['client_name'])) {
                    continue;
                }

                try {
                    $clientName = trim((string) $data['client_name']);
                    if (!isset($clientCache[$clientName])) {
                        $clientCache[$clientName] = Client::where('name', $clientName)->first();
                        if (!$clientCache[$clientName]) {
                            $docNumber = 'IMP-' . substr(md5($clientName), 0, 10);
                            $clientCache[$clientName] = Client::create([
                                'name'            => $clientName,
                                'code'            => $docNumber,
                                'document_type'   => 'NIT',
                                'document_number' => $docNumber,
                                'active'          => true,
                            ]);
                        }
                    }
                    $client = $clientCache[$clientName];

                    $docNumber    = trim((string) ($data['document_number'] ?? ''));
                    $portfolioDoc = null;
                    if ($docNumber) {
                        $portfolioDoc = PortfolioDocument::where('document_number', $docNumber)
                            ->where('client_id', $client->id)
                            ->first();
                    }

                    $appliedAmount = (float) ($data['applied_amount'] ?? 0);
                    $paymentDate   = $this->parseDate($data['application_date'] ?? $data['payment_date'] ?? null);
                    $docType       = self::TYPE_MAP[trim($data['document_type'] ?? '')] ?? ($data['document_type'] ?? null);

                    CollectionDetail::create([
                        'collection_load_id'    => $load->id,
                        'client_id'             => $client->id,
                        'portfolio_document_id' => $portfolioDoc?->id,
                        'receipt_number'        => $data['receipt_number'] ?? null,
                        'reconciliation_id'     => $data['reconciliation_id'] ?? null,
                        'document_number'       => $docNumber ?: null,
                        'applied_document_type' => $docType,
                        'amount'                => $appliedAmount,
                        'applied_amount'        => $appliedAmount,
                        'pending_amount_after'  => (float) ($data['pending_amount_after'] ?? 0),
                        'payment_date'          => $paymentDate,
                        'regional'              => $data['regional'] ?? null,
                        'channel'               => $data['channel'] ?? null,
                    ]);

                    if ($portfolioDoc && $appliedAmount > 0) {
                        $portfolioDoc->decrement('pending_amount', $appliedAmount);
                        $portfolioDoc->increment('collected_amount', $appliedAmount);
                    }

                    $totalCollected += $appliedAmount;
                    $processed++;
                } catch (\Throwable $e) {
                    $errCount++;
                    $errors[] = ['row' => $rowNumber, 'error' => $e->getMessage()];
                    Log::warning("CollectionImport row {$rowNumber}: " . $e->getMessage());
                }
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $errCount += \count($chunk);
            $errors[]  = ['chunk_error' => $e->getMessage()];
            Log::error("CollectionImport chunk rollback: " . $e->getMessage());
        }

        return [$processed, $errCount, $totalCollected, $errors];
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
            if ($d) {
                return $d->format('Y-m-d');
            }
        }
        return null;
    }
}
