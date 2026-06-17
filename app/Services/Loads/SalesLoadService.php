<?php

namespace App\Services\Loads;

use App\Data\Loads\LoadProcessingResultData;
use App\Data\Loads\LoadValidationResultData;
use App\Models\SalesLoad;
use App\Models\SalesRow;
use App\Models\User;
use App\Services\Loads\Support\RemoteSpreadsheetFetcher;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SalesLoadService
{
    public function __construct(
        private readonly SalesLoadValidationService $validationService,
        private readonly RemoteSpreadsheetFetcher $remoteFetcher,
        private readonly LoadAuditService $auditService,
    ) {}

    public function handleFromUrl(string $url, ?string $notes, User $user): LoadProcessingResultData
    {
        $fetched = $this->remoteFetcher->fetch($url, 'sales-loads');

        $load = SalesLoad::query()->create([
            'reference' => 'SV-' . Str::upper(Str::random(8)),
            'original_filename' => $fetched['original_filename'],
            'source_url' => $this->remoteFetcher->sanitizeSourceUrl($url),
            'disk' => 'local',
            'path' => $fetched['storage_path'],
            'file_hash' => $fetched['file_hash'],
            'status' => 'pending',
            'notes' => $notes,
            'uploaded_by' => $user->id,
        ]);

        $this->auditService->record($load, 'sales', 'created', 'Carga de ventas registrada desde Azure.', $user);

        return $this->processValidatedFile($load, $fetched['absolute_path'], $user);
    }

    public function handleUpload(UploadedFile $uploadedFile, ?string $notes, User $user): LoadProcessingResultData
    {
        $extension = strtolower($uploadedFile->getClientOriginalExtension());

        if (! in_array($extension, ['csv', 'xlsx', 'xls'], true)) {
            throw new \DomainException('La carga de ventas solo permite archivos csv, xlsx o xls.');
        }

        $storagePath = $uploadedFile->store('sales-loads', 'local');
        $absolutePath = Storage::disk('local')->path($storagePath);

        $load = SalesLoad::query()->create([
            'reference' => 'SV-' . Str::upper(Str::random(8)),
            'original_filename' => $uploadedFile->getClientOriginalName(),
            'disk' => 'local',
            'path' => $storagePath,
            'file_hash' => hash_file('sha256', $absolutePath),
            'status' => 'pending',
            'notes' => $notes,
            'uploaded_by' => $user->id,
        ]);

        $this->auditService->record($load, 'sales', 'created', 'Carga de ventas registrada manualmente.', $user);

        return $this->processValidatedFile($load, $absolutePath, $user);
    }

    private function processValidatedFile(SalesLoad $load, string $absolutePath, User $user): LoadProcessingResultData
    {
        $validation = $this->validationService->validate($absolutePath, $load->original_filename);

        $this->persistValidationState($load, $validation);

        if (! $validation->isValid) {
            $load->forceFill([
                'status' => 'rejected',
                'processed_at' => now(),
            ])->save();

            $this->auditService->record(
                $load,
                'sales',
                'rejected',
                'Carga de ventas rechazada por validación.',
                $user,
                $validation->toSummaryArray(),
            );

            return $this->buildResult($load, $validation);
        }

        try {
            DB::transaction(function () use ($load, $validation, $user): void {
                $load->forceFill([
                    'status' => 'processing',
                    'period_key' => $validation->periodKey,
                ])->save();

                $totalAmount = 0.0;
                $now = now();

                foreach (array_chunk($validation->normalizedRows, 500) as $chunk) {
                    $batch = [];

                    foreach ($chunk as $row) {
                        $totalAmount += (float) $row['sale_amount'];
                        $batch[] = array_merge($row, [
                            'sales_load_id' => $load->id,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);
                    }

                    SalesRow::query()->insert($batch);
                }

                $load->forceFill([
                    'status' => 'completed',
                    'processed_rows' => $validation->validRows,
                    'total_amount' => $totalAmount,
                    'processed_at' => now(),
                ])->save();
            });

            $this->auditService->record(
                $load,
                'sales',
                'completed',
                'Carga de ventas completada.',
                $user,
                $validation->toSummaryArray(),
            );
        } catch (\Throwable $exception) {
            $load->forceFill([
                'status' => 'failed',
                'processed_at' => now(),
                'error_log' => array_merge($load->error_log ?? [], [
                    'failure' => $exception->getMessage(),
                ]),
            ])->save();

            throw $exception;
        }

        return $this->buildResult($load->fresh(), $validation);
    }

    private function persistValidationState(SalesLoad $load, LoadValidationResultData $validation): void
    {
        $load->forceFill([
            'period_key' => $validation->periodKey,
            'total_rows' => $validation->totalRows,
            'valid_rows' => $validation->validRows,
            'error_rows' => $validation->errorRows,
            'validation_summary' => $validation->toSummaryArray(),
            'error_log' => $validation->errorPreview(50),
        ])->save();
    }

    private function buildResult(SalesLoad $load, LoadValidationResultData $validation): LoadProcessingResultData
    {
        return new LoadProcessingResultData(
            module: 'sales',
            loadId: $load->id,
            reference: $load->reference,
            status: $load->status,
            periodKey: $load->period_key,
            version: 0,
            totalRows: $validation->totalRows,
            validRows: $validation->validRows,
            processedRows: (int) $load->processed_rows,
            errorRows: $validation->errorRows,
            emptyRows: $validation->emptyRows,
            duplicateRows: $validation->duplicateRows,
            totalAmount: (float) $load->total_amount,
            itemCount: (int) $load->processed_rows,
            errorPreview: $validation->errorPreview(),
            summary: $validation->summary,
        );
    }
}