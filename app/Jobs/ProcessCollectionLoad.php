<?php

namespace App\Jobs;

use App\Models\CollectionLoad;
use App\Models\User;
use App\Services\ConciliationService;
use App\Services\Loads\CollectionLoadValidationService;
use App\Services\Loads\LoadAuditService;
use App\Services\Loads\PeriodControlService;
use DomainException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Client;
use App\Models\CollectionDetail;
use App\Models\PortfolioDocument;
use Illuminate\Support\Str;

class ProcessCollectionLoad implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 1800;

    public int $tries = 1;

    public function __construct(
        public readonly int $loadId,
        public readonly int $userId,
    ) {}

    public function handle(
        CollectionLoadValidationService $validationService,
        PeriodControlService $periodControlService,
        LoadAuditService $auditService,
        ConciliationService $conciliationService,
    ): void {
        ini_set('memory_limit', '512M');

        $load = CollectionLoad::findOrFail($this->loadId);
        $user = User::findOrFail($this->userId);
        $absolutePath = Storage::disk('local')->path($load->path);

        $validation = $validationService->validate($absolutePath);

        $this->persistValidationState($load, $validation);

        if (! $validation->isValid) {
            $load->forceFill(['status' => 'rejected', 'processed_at' => now()])->save();
            $auditService->record($load, 'collection', 'rejected', 'Carga rechazada por validación.', $user, $validation->toSummaryArray());
            return;
        }

        try {
            DB::transaction(function () use ($load, $validation, $periodControlService): void {
                $version = $periodControlService->nextCollectionVersion($validation->periodDate);

                $load->forceFill([
                    'status'      => 'processing',
                    'period_key'  => $validation->periodKey,
                    'period_date' => $validation->periodDate?->toDateString(),
                    'version'     => $version,
                ])->save();

                $clientCache  = [];
                $detailCount  = 0;
                $totalCollected = 0.0;

                foreach ($validation->normalizedRows as $row) {
                    $portfolioDocument = PortfolioDocument::query()
                        ->where('document_number', $row['document_number'])
                        ->where('period_date', $validation->periodDate?->toDateString())
                        ->whereHas('portfolioLoad', fn ($q) => $q->where('is_active', true)->where('status', 'completed'))
                        ->latest('id')
                        ->first();

                    $client       = $portfolioDocument?->client ?? $this->resolveClient($row, $clientCache);
                    $pendingAfter = $portfolioDocument
                        ? max(0, (float) $portfolioDocument->pending_amount - (float) $row['amount'])
                        : null;

                    CollectionDetail::query()->create([
                        'collection_load_id'    => $load->id,
                        'row_number'            => $row['row_number'],
                        'client_id'             => $client->id,
                        'client_name'           => $client->name,
                        'portfolio_document_id' => $portfolioDocument?->id,
                        'document_number'       => $row['document_number'],
                        'document_type'         => $row['document_type'] ?? null,
                        'receipt_number'        => $row['receipt_number'],
                        'applied_document_type' => $portfolioDocument?->document_type ?? $row['document_type'] ?? null,
                        'amount'                => $row['amount'],
                        'applied_amount'        => $row['amount'],
                        'pending_amount_after'  => $pendingAfter,
                        'payment_date'          => $row['payment_date'],
                        'notes'                 => $row['notes'],
                        'regional'              => $client->region,
                        'channel'               => $client->channel,
                        'uen'                   => $client->uen,
                        'seller_name'           => $row['seller_name'],
                        'source_payload'        => $row['source_payload'],
                        'period_key'            => $validation->periodKey,
                        'period_date'           => $validation->periodDate?->toDateString(),
                    ]);

                    $detailCount++;
                    $totalCollected += (float) $row['amount'];
                }

                $load->forceFill([
                    'status'         => 'completed',
                    'period_key'     => $validation->periodKey,
                    'period_date'    => $validation->periodDate?->toDateString(),
                    'version'        => $version,
                    'processed_rows' => count($validation->normalizedRows),
                    'detail_count'   => $detailCount,
                    'total_collected'=> round($totalCollected, 2),
                    'processed_at'   => now(),
                ])->save();

                $periodControlService->activateCollectionLoad($load);
            });

            $conciliationService->reconcileLoad($load->fresh());

        } catch (\Throwable $exception) {
            $load->forceFill([
                'status'      => 'failed',
                'processed_at'=> now(),
                'error_log'   => [['message' => $exception->getMessage(), 'error_code' => 'processing_failure']],
            ])->save();

            $auditService->record($load, 'collection', 'failed', 'Carga fallida.', $user, ['message' => $exception->getMessage()]);
            throw $exception;
        }

        $auditService->record(
            $load->fresh(),
            'collection',
            'completed',
            'Carga de recaudos completada y activada.',
            $user,
            ['period_key' => $load->period_key, 'version' => $load->version, 'total_collected' => $load->total_collected],
        );
    }

    private function persistValidationState(CollectionLoad $load, $validation): void
    {
        $load->forceFill([
            'period_key'         => $validation->periodKey,
            'period_date'        => $validation->periodDate?->toDateString(),
            'total_rows'         => $validation->totalRows,
            'valid_rows'         => $validation->validRows,
            'error_rows'         => $validation->errorRows,
            'empty_rows'         => $validation->emptyRows,
            'duplicate_rows'     => $validation->duplicateRows,
            'validation_summary' => $validation->toSummaryArray(),
            'error_log'          => $validation->errorPreview(10),
        ])->save();

        $load->errors()->delete();

        if ($validation->errors !== []) {
            $load->errors()->createMany(array_map(
                static fn ($e) => $e->toArray(),
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
            $client = Client::query()->where('name', $row['client_name'])->first()
                ?? Client::query()->create([
                    'code'            => 'REC-' . Str::upper(Str::random(6)),
                    'name'            => $row['client_name'],
                    'document_type'   => 'NIT',
                    'document_number' => 'REC-' . substr(md5($row['client_name']), 0, 10),
                    'active'          => true,
                ]);
        } else {
            $client = Client::query()->firstOrCreate(
                ['document_number' => $row['document_number']],
                [
                    'code'          => $row['document_number'],
                    'name'          => 'Cliente ' . $row['document_number'],
                    'document_type' => 'NIT',
                    'active'        => true,
                ],
            );
        }

        return $cache[$key] = $client;
    }
}
