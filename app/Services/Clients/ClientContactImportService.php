<?php

namespace App\Services\Clients;

use App\Data\Loads\LoadValidationErrorData;
use App\Models\Client;
use App\Models\ClientContactLoad;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ClientContactImportService
{
    public function __construct(
        private readonly ClientContactImportValidationService $validationService,
        private readonly ClientNitMatcher $nitMatcher,
    ) {}

    /**
     * @return array{
     *     load: ClientContactLoad,
     *     updated_rows: int,
     *     not_found_rows: int,
     *     error_rows: int,
     *     skipped_rows: int,
     *     error_preview: array<int, array<string, mixed>>,
     * }
     */
    public function handleUpload(UploadedFile $uploadedFile, User $user): array
    {
        $extension = strtolower($uploadedFile->getClientOriginalExtension());

        if (! in_array($extension, ['csv', 'xlsx', 'xls'], true)) {
            throw new \DomainException('La actualización de contactos solo permite archivos csv, xlsx o xls.');
        }

        $storagePath = $uploadedFile->store('client-contact-loads', 'local');
        $absolutePath = Storage::disk('local')->path($storagePath);

        $load = ClientContactLoad::query()->create([
            'reference' => 'CC-' . Str::upper(Str::random(8)),
            'original_filename' => $uploadedFile->getClientOriginalName(),
            'disk' => 'local',
            'path' => $storagePath,
            'status' => 'processing',
            'uploaded_by' => $user->id,
        ]);

        $validation = $this->validationService->validate($absolutePath);
        $errorPreview = array_map(
            static fn (LoadValidationErrorData $error): array => $error->toArray(),
            array_slice($validation['errors'], 0, 50),
        );

        if (! $validation['is_valid']) {
            $load->forceFill([
                'status' => 'rejected',
                'total_rows' => $validation['total_rows'],
                'skipped_rows' => $validation['skipped_rows'],
                'error_rows' => $validation['error_rows'],
                'error_log' => $errorPreview,
                'processed_at' => now(),
            ])->save();

            return [
                'load' => $load->fresh(),
                'updated_rows' => 0,
                'not_found_rows' => 0,
                'error_rows' => $validation['error_rows'],
                'skipped_rows' => $validation['skipped_rows'],
                'error_preview' => $errorPreview,
            ];
        }

        $updatedRows = 0;
        $notFoundRows = 0;
        $runtimeErrors = [];

        DB::transaction(function () use ($validation, &$updatedRows, &$notFoundRows, &$runtimeErrors): void {
            foreach ($validation['rows'] as $row) {
                $client = $this->resolveClient($row);

                if ($client === null) {
                    $notFoundRows++;
                    $runtimeErrors[] = [
                        'row_number' => $row['row_number'],
                        'field' => 'nit',
                        'error_code' => 'not_found',
                        'message' => 'Cliente no encontrado con el NIT indicado.',
                    ];
                    continue;
                }

                $updates = collect($row)
                    ->except(['row_number', 'document_number', 'code'])
                    ->filter(fn ($value) => $value !== null && $value !== '')
                    ->all();

                if ($updates === []) {
                    continue;
                }

                $client->fill($updates)->save();
                $updatedRows++;
            }
        });

        $totalErrors = count($runtimeErrors);
        $status = $updatedRows > 0 && $totalErrors === 0
            ? 'completed'
            : ($updatedRows > 0 ? 'completed' : 'rejected');

        if ($updatedRows === 0 && $notFoundRows > 0 && $totalErrors === $notFoundRows) {
            $status = 'rejected';
        }

        $load->forceFill([
            'status' => $status,
            'total_rows' => $validation['total_rows'],
            'updated_rows' => $updatedRows,
            'not_found_rows' => $notFoundRows,
            'skipped_rows' => $validation['skipped_rows'],
            'error_rows' => $totalErrors,
            'error_log' => $runtimeErrors !== [] ? array_slice($runtimeErrors, 0, 200) : null,
            'processed_at' => now(),
        ])->save();

        return [
            'load' => $load->fresh(),
            'updated_rows' => $updatedRows,
            'not_found_rows' => $notFoundRows,
            'error_rows' => $totalErrors,
            'skipped_rows' => $validation['skipped_rows'],
            'error_preview' => array_slice($runtimeErrors, 0, 50),
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function resolveClient(array $row): ?Client
    {
        $client = $this->nitMatcher->find($row['document_number'] ?? null);

        if ($client !== null) {
            return $client;
        }

        if (! empty($row['code'])) {
            return $this->nitMatcher->find($row['code']);
        }

        return null;
    }
}
