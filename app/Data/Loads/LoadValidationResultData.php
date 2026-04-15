<?php

namespace App\Data\Loads;

use Carbon\CarbonImmutable;

final readonly class LoadValidationResultData
{
    /**
     * @param  array<int, array<string, mixed>>  $normalizedRows
     * @param  array<int, LoadValidationErrorData>  $errors
     * @param  array<string, mixed>  $summary
     */
    public function __construct(
        public bool $isValid,
        public ?string $detectedModule,
        public ?string $periodKey,
        public ?CarbonImmutable $periodDate,
        public array $normalizedRows,
        public array $errors,
        public int $totalRows = 0,
        public int $validRows = 0,
        public int $errorRows = 0,
        public int $emptyRows = 0,
        public int $duplicateRows = 0,
        public array $summary = [],
    ) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function errorPreview(int $limit = 5): array
    {
        return array_map(
            static fn (LoadValidationErrorData $error): array => $error->toArray(),
            array_slice($this->errors, 0, $limit),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toSummaryArray(): array
    {
        return array_filter([
            'detected_module' => $this->detectedModule,
            'period_key' => $this->periodKey,
            'total_rows' => $this->totalRows,
            'valid_rows' => $this->validRows,
            'error_rows' => $this->errorRows,
            'empty_rows' => $this->emptyRows,
            'duplicate_rows' => $this->duplicateRows,
            'preview_errors' => $this->errorPreview(),
            'summary' => $this->summary,
        ], static fn ($value) => $value !== null);
    }
}
