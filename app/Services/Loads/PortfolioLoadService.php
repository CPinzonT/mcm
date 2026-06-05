<?php

namespace App\Services\Loads;

use App\Data\Loads\LoadProcessingResultData;
use App\Models\Advisor;
use App\Models\Client;
use App\Models\ClientHistory;
use App\Models\PortfolioDocument;
use App\Models\PortfolioLoad;
use App\Models\User;
use App\Services\PortfolioRotationSnapshotService;
use App\Services\Risk\RiskClassificationService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PortfolioLoadService
{
    public function __construct(
        private readonly PortfolioLoadValidationService $validationService,
        private readonly PeriodControlService $periodControlService,
        private readonly LoadAuditService $auditService,
        private readonly RiskClassificationService $riskClassification,
    ) {}

    public function handleUpload(UploadedFile $uploadedFile, ?string $notes, User $user, ?string $forcedPeriodKey = null): LoadProcessingResultData
    {
        $extension = strtolower($uploadedFile->getClientOriginalExtension());

        if (! in_array($extension, ['csv', 'xlsx', 'xls'], true)) {
            throw new \DomainException('La carga de cartera solo permite archivos csv, xlsx o xls.');
        }

        $storagePath = $this->storeUploadedFile($uploadedFile, 'portfolio-loads');
        $absolutePath = Storage::disk('local')->path($storagePath);

        $load = PortfolioLoad::query()->create([
            'reference' => 'PL-' . Str::upper(Str::random(8)),
            'original_filename' => $uploadedFile->getClientOriginalName(),
            'disk' => 'local',
            'path' => $storagePath,
            'file_hash' => hash_file('sha256', $absolutePath),
            'status' => 'pending',
            'notes' => $notes,
            'uploaded_by' => $user->id,
        ]);

        $this->auditService->record($load, 'portfolio', 'created', 'Carga de cartera registrada.', $user);

        $validation = $this->validationService->validate(
            $absolutePath,
            $forcedPeriodKey,
            $uploadedFile->getClientOriginalName(),
        );

        $this->persistValidationState($load, $validation);

        if (! $validation->isValid) {
            $load->forceFill([
                'status' => 'rejected',
                'processed_at' => now(),
            ])->save();

            $this->auditService->record(
                $load,
                'portfolio',
                'rejected',
                'Carga de cartera rechazada por validacion.',
                $user,
                $validation->toSummaryArray(),
            );

            return $this->buildResult($load);
        }

        try {
            DB::transaction(function () use ($load, $validation, $user): void {
                $version = $this->periodControlService->nextPortfolioVersion($validation->periodDate);
                $comparisonLoad = PortfolioLoad::query()
                    ->where('status', 'completed')
                    ->where('is_active', true)
                    ->orderByDesc('period_date')
                    ->orderByDesc('version')
                    ->first();

                $load->forceFill([
                    'status' => 'processing',
                    'period_key' => $validation->periodKey,
                    'period_date' => $validation->periodDate?->toDateString(),
                    'version' => $version,
                ])->save();

                $clientCache = [];
                $advisorCache = [];
                $importedKeys = [];
                $documentCount = 0;
                $pendingTotal = 0.0;

                foreach ($validation->normalizedRows as $row) {
                    $client = $this->resolveClient($row, $clientCache);
                    $advisor = $this->resolveAdvisor($row['sales_employee'] ?? null, $advisorCache);

                    PortfolioDocument::query()->create([
                        'client_id' => $client->id,
                        'portfolio_load_id' => $load->id,
                        'advisor_id' => $advisor?->id,
                        'account' => $row['account'],
                        'logical_key' => $row['logical_key'],
                        'document_number' => $row['document_number'],
                        'client_reference' => $row['client_reference'],
                        'document_type' => $row['document_type'],
                        'issue_date' => $row['issue_date'],
                        'activation_date' => $row['activation_date'],
                        'due_date' => $row['due_date'],
                        'original_amount' => $row['original_amount'],
                        'pending_amount' => $row['pending_amount'],
                        'collected_amount' => max(0, $row['original_amount'] - $row['pending_amount']),
                        'days_overdue' => $row['days_overdue'],
                        'aging_buckets' => $row['aging_buckets'],
                        'risk_level' => $this->riskClassification->riskLevelForDays($row['days_overdue']),
                        'status' => $row['pending_amount'] <= 0 ? 'paid' : 'active',
                        'currency' => Str::upper($row['currency'] ?? 'COP'),
                        'period_date' => $row['period_date'],
                        'notes' => $load->notes,
                    ]);

                    $importedKeys[$row['logical_key']] = true;
                    $documentCount++;
                    $pendingTotal += (float) $row['pending_amount'];
                }

                if ($comparisonLoad) {
                    $missingDocuments = $comparisonLoad->portfolioDocuments()
                        ->whereIn('status', ['active', 'partial', 'in_process'])
                        ->whereNotIn('logical_key', array_keys($importedKeys))
                        ->get();

                    foreach ($missingDocuments as $previousDocument) {
                        PortfolioDocument::query()->create([
                            'client_id' => $previousDocument->client_id,
                            'portfolio_load_id' => $load->id,
                            'advisor_id' => $previousDocument->advisor_id,
                            'account' => $previousDocument->account,
                            'logical_key' => $previousDocument->logical_key,
                            'document_number' => $previousDocument->document_number,
                            'client_reference' => $previousDocument->client_reference,
                            'document_type' => $previousDocument->document_type,
                            'issue_date' => $previousDocument->issue_date,
                            'activation_date' => $previousDocument->activation_date,
                            'due_date' => $previousDocument->due_date,
                            'original_amount' => $previousDocument->original_amount,
                            'pending_amount' => 0,
                            'collected_amount' => $previousDocument->collected_amount,
                            'days_overdue' => $previousDocument->days_overdue,
                            'aging_buckets' => [
                                'actual' => 0,
                                '1_30_dias' => 0,
                                '31_60_dias' => 0,
                                '61_90_dias' => 0,
                                '91_180_dias' => 0,
                                '181_360_dias' => 0,
                                '361_dias' => 0,
                            ],
                            'risk_level' => $previousDocument->risk_level,
                            'status' => 'closed',
                            'currency' => $previousDocument->currency,
                            'period_date' => $validation->periodDate?->toDateString(),
                            'notes' => 'Documento ausente en el corte importado.',
                            'closed_at' => now(),
                            'closure_reason' => "Documento ausente en el corte {$validation->periodKey}.",
                        ]);
                    }
                }

                $load->forceFill([
                    'status' => 'completed',
                    'period_key' => $validation->periodKey,
                    'period_date' => $validation->periodDate?->toDateString(),
                    'version' => $version,
                    'processed_rows' => count($validation->normalizedRows),
                    'document_count' => $documentCount,
                    'total_pending_amount' => round($pendingTotal, 2),
                    'processed_at' => now(),
                ])->save();

                $this->periodControlService->activatePortfolioLoad($load);
            });
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
                'portfolio',
                'failed',
                'Carga de cartera fallida durante procesamiento.',
                $user,
                ['message' => $exception->getMessage()],
            );

            throw $exception;
        }

        app(PortfolioRotationSnapshotService::class)->generateForLoad($load->fresh());

        $this->auditService->record(
            $load->fresh(),
            'portfolio',
            'completed',
            'Carga de cartera completada y activada.',
            $user,
            [
                'period_key' => $load->period_key,
                'version' => $load->version,
                'processed_rows' => $load->processed_rows,
            ],
        );

        return $this->buildResult($load->fresh(['errors']));
    }

    private function persistValidationState(PortfolioLoad $load, $validation): void
    {
        $load->forceFill([
            'period_key' => $validation->periodKey,
            'period_date' => $validation->periodDate?->toDateString(),
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
        $key = $row['nit'] ?: $row['client_name'];

        if (isset($cache[$key])) {
            return $cache[$key];
        }

        $documentNumber = $row['nit'] ?: ('IMP-' . substr(md5($row['client_name']), 0, 10));

        $client = Client::withTrashed()->where('document_number', $documentNumber)->first();

        if ($client === null) {
            $client = new Client([
                'code' => $documentNumber,
                'name' => $row['client_name'],
                'document_type' => 'NIT',
                'document_number' => $documentNumber,
                'active' => true,
            ]);
        } elseif ($client->trashed()) {
            $client->restore();
        }

        $isNew = ! $client->exists;

        $fill = [
            'name' => $row['client_name'] ?: $client->name,
            'phone' => $row['phone'] ?: $client->phone,
            'address' => $row['address'] ?: $client->address,
            'region' => $row['regional'] ?: $client->region,
            'channel' => $row['channel'] ?: $client->channel,
            'uen' => $row['uen'] ?: $client->uen,
            'contact_name' => $row['contact'] ?: $client->contact_name,
            'active' => true,
        ];

        if (! empty($row['payment_term_days'])) {
            $fill['payment_term_days'] = (int) $row['payment_term_days'];
        } elseif (! empty($row['issue_date']) && ! empty($row['due_date'])) {
            $issue = \Carbon\CarbonImmutable::parse($row['issue_date']);
            $due = \Carbon\CarbonImmutable::parse($row['due_date']);
            $termDays = max(0, (int) $issue->diffInDays($due));
            if ($termDays > 0) {
                $fill['payment_term_days'] = $termDays;
            }
        }

        $client->fill($fill)->save();

        ClientHistory::query()->create([
            'client_id'   => $client->id,
            'event_date'  => now(),
            'event_type'  => $isNew ? 'created' : 'portfolio_load',
            'amount'      => 0,
            'description' => $isNew
                ? "Cliente creado en carga de cartera — {$client->document_number}"
                : "Datos actualizados en carga de cartera — {$client->document_number}",
        ]);

        return $cache[$key] = $client;
    }

    private function resolveAdvisor(?string $advisorName, array &$cache): ?Advisor
    {
        $advisorName = trim((string) $advisorName);

        if ($advisorName === '') {
            return null;
        }

        if (isset($cache[$advisorName])) {
            return $cache[$advisorName];
        }

        return $cache[$advisorName] = Advisor::query()->firstOrCreate(
            ['name' => $advisorName],
            [
                'code' => 'IMP-' . Str::upper(Str::random(6)),
                'active' => true,
            ],
        );
    }

    private function buildResult(PortfolioLoad $load): LoadProcessingResultData
    {
        return new LoadProcessingResultData(
            module: 'portfolio',
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
            totalAmount: (float) $load->total_pending_amount,
            itemCount: (int) $load->document_count,
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
