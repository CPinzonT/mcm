<?php

namespace App\Services\Loads;

use App\Data\Loads\LoadProcessingResultData;
use App\Models\Client;
use App\Models\CollectionDetail;
use App\Models\CollectionLoad;
use App\Models\PortfolioDocument;
use App\Models\User;
use App\Services\ConciliationService;
use App\Services\Loads\Concerns\ResolvesCollectionPortfolioDocument;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CollectionLoadService
{
    use ResolvesCollectionPortfolioDocument;

    public function __construct(
        private readonly CollectionLoadValidationService $validationService,
        private readonly PeriodControlService $periodControlService,
        private readonly LoadAuditService $auditService,
        private readonly ConciliationService $conciliationService,
    ) {}

    public function storeAndRegister(UploadedFile $uploadedFile, ?string $notes, User $user): CollectionLoad
    {
        $extension = strtolower($uploadedFile->getClientOriginalExtension());

        if (! in_array($extension, ['csv', 'xlsx'], true)) {
            throw new \DomainException('La carga de recaudos solo permite archivos csv o xlsx.');
        }

        $storagePath  = $this->storeUploadedFile($uploadedFile, 'collection-loads');
        $absolutePath = Storage::disk('local')->path($storagePath);

        $load = CollectionLoad::query()->create([
            'reference'         => 'RC-' . Str::upper(Str::random(8)),
            'original_filename' => $uploadedFile->getClientOriginalName(),
            'disk'              => 'local',
            'path'              => $storagePath,
            'file_hash'         => hash_file('sha256', $absolutePath),
            'status'            => 'pending',
            'notes'             => $notes,
            'uploaded_by'       => $user->id,
        ]);

        $this->auditService->record($load, 'collection', 'created', 'Carga de recaudos registrada.', $user);

        return $load;
    }

    public function handleUpload(UploadedFile $uploadedFile, ?string $notes, User $user): LoadProcessingResultData
    {
        $load         = $this->storeAndRegister($uploadedFile, $notes, $user);
        $absolutePath = Storage::disk('local')->path($load->path);

        $validation = $this->validationService->validate($absolutePath, $uploadedFile->getClientOriginalName());

        $this->persistValidationState($load, $validation);

        if (! $validation->isValid) {
            $load->forceFill([
                'status' => 'rejected',
                'processed_at' => now(),
            ])->save();

            $this->auditService->record(
                $load,
                'collection',
                'rejected',
                'Carga de recaudos rechazada por validacion.',
                $user,
                $validation->toSummaryArray(),
            );

            return $this->buildResult($load);
        }

        try {
            DB::transaction(function () use ($load, $validation, $user): void {
                $version = $this->periodControlService->nextCollectionVersion();

                $load->forceFill([
                    'status' => 'processing',
                    'period_key' => null,
                    'period_date' => null,
                    'version' => $version,
                ])->save();

                $clientCache = [];
                $detailCount = 0;
                $totalCollected = 0.0;
                $portfolioIndex = $this->buildPortfolioDocumentIndex();
                $now = now()->toDateTimeString();

                foreach (array_chunk($validation->normalizedRows, 400) as $chunk) {
                    $batch = [];

                    foreach ($chunk as $row) {
                        $portfolioDocument = $this->resolveCollectionPortfolioDocument($row, $portfolioIndex);
                        $client = $portfolioDocument?->client ?? $this->resolveClient($row, $clientCache);
                        $pendingAfter = $portfolioDocument
                            ? max(0, (float) $portfolioDocument->pending_amount - (float) $row['amount'])
                            : null;

                        $batch[] = [
                            'collection_load_id' => $load->id,
                            'row_number' => $row['row_number'],
                            'client_id' => $client->id,
                            'client_name' => $client->name,
                            'portfolio_document_id' => $portfolioDocument?->id,
                            'document_number' => $row['document_number'],
                            'document_type' => $row['document_type'] ?? null,
                            'receipt_number' => $row['receipt_number'],
                            'reconciliation_id' => $row['reconciliation_id'] ?? null,
                            'applied_document_type' => $portfolioDocument?->document_type ?? $row['document_type'] ?? null,
                            'amount' => $row['amount'],
                            'applied_amount' => $row['amount'],
                            'pending_amount_after' => $pendingAfter,
                            'payment_date' => $row['payment_date'],
                            'notes' => $row['notes'],
                            'regional' => $row['regional'] ?? $client->region,
                            'channel' => $row['channel'] ?? $client->channel,
                            'uen' => $row['uen'] ?? $client->uen,
                            'seller_name' => $row['seller_name'],
                            'source_payload' => json_encode($row['source_payload'] ?? [], JSON_UNESCAPED_UNICODE),
                            'period_key' => null,
                            'period_date' => null,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];

                        $detailCount++;
                        $totalCollected += (float) $row['amount'];
                    }

                    DB::table('collection_details')->insert($batch);
                }

                $load->forceFill([
                    'status' => 'completed',
                    'period_key' => null,
                    'period_date' => null,
                    'version' => $version,
                    'processed_rows' => count($validation->normalizedRows),
                    'detail_count' => $detailCount,
                    'total_collected' => round($totalCollected, 2),
                    'processed_at' => now(),
                ])->save();

                $this->periodControlService->activateCollectionLoad($load);
            });

            $this->conciliationService->reconcileLoad($load->fresh());
        } catch (\Throwable $exception) {
            $load->forceFill([
                'status' => 'failed',
                'processed_at' => now(),
                'error_log' => [[
                    'message' => $exception->getMessage(),
                    'error_code' => 'processing_failure',
                ]],
            ])->save();

            $this->auditService->record(
                $load,
                'collection',
                'failed',
                'Carga de recaudos fallida durante procesamiento.',
                $user,
                ['message' => $exception->getMessage()],
            );

            throw $exception;
        }

        $this->auditService->record(
            $load->fresh(),
            'collection',
            'completed',
            'Carga de recaudos completada y activada.',
            $user,
            [
                'period_key' => $load->period_key,
                'version' => $load->version,
                'processed_rows' => $load->processed_rows,
                'total_collected' => $load->total_collected,
            ],
        );

        return $this->buildResult($load->fresh(['errors']));
    }

    private function persistValidationState(CollectionLoad $load, $validation): void
    {
        $load->forceFill([
            'period_key' => null,
            'period_date' => null,
            'total_rows' => $validation->totalRows,
            'valid_rows' => $validation->validRows,
            'error_rows' => $validation->errorRows,
            'empty_rows' => $validation->emptyRows,
            'duplicate_rows' => $validation->duplicateRows,
            'validation_summary' => $validation->toSummaryArray(),
            'error_log' => $validation->errorPreview(10),
        ])->save();

        $load->errors()->delete();

        if ($validation->errors !== []) {
            $load->errors()->createMany(array_map(
                static fn ($error) => $error->toArray(),
                $validation->errors,
            ));
        }
    }

    private function resolveClient(array $row, array &$cache): Client
    {
        $key = $row['client_name'] ?: $row['document_number'];

        if (isset($cache[$key])) {
            return $cache[$key];
        }

        if ($row['client_name']) {
            $client = Client::query()
                ->whereRaw('UPPER(TRIM(name)) = ?', [mb_strtoupper(trim($row['client_name']))])
                ->first();

            if (! $client) {
                $client = Client::query()
                    ->get(['id', 'name'])
                    ->first(fn (Client $candidate) => $this->namesMatchForCollection($row['client_name'], $candidate->name));
            }

            if (! $client) {
                $client = Client::query()->create([
                    'code' => 'REC-' . Str::upper(Str::random(6)),
                    'name' => $row['client_name'],
                    'document_type' => 'NIT',
                    'document_number' => 'REC-' . substr(md5($row['client_name']), 0, 10),
                    'active' => true,
                ]);
            }
        } else {
            $client = Client::query()->firstOrCreate(
                ['document_number' => $row['document_number']],
                [
                    'code' => $row['document_number'],
                    'name' => 'Cliente ' . $row['document_number'],
                    'document_type' => 'NIT',
                    'active' => true,
                ],
            );
        }

        return $cache[$key] = $client;
    }

    private function buildResult(CollectionLoad $load): LoadProcessingResultData
    {
        return new LoadProcessingResultData(
            module: 'collection',
            loadId: $load->id,
            reference: $load->reference,
            status: $load->status,
            periodKey: $load->period_key,
            version: (int) $load->version,
            totalRows: (int) $load->total_rows,
            validRows: (int) $load->valid_rows,
            processedRows: (int) $load->processed_rows,
            errorRows: (int) $load->error_rows,
            emptyRows: (int) $load->empty_rows,
            duplicateRows: (int) $load->duplicate_rows,
            totalAmount: (float) $load->total_collected,
            itemCount: (int) $load->detail_count,
            errorPreview: $load->error_log ?? [],
            summary: $load->validation_summary ?? [],
        );
    }

    private function storeUploadedFile(UploadedFile $uploadedFile, string $directory): string
    {
        $extension = strtolower($uploadedFile->getClientOriginalExtension());
        $name = Str::slug(pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME));
        $name = $name !== '' ? $name : 'archivo';
        $targetFile = now()->format('YmdHis') . '-' . Str::random(8) . "-{$name}.{$extension}";

        return $uploadedFile->storeAs($directory, $targetFile, 'local');
    }
}
