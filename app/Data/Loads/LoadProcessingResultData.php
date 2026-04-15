<?php

namespace App\Data\Loads;

final readonly class LoadProcessingResultData
{
    /**
     * @param  array<int, array<string, mixed>>  $errorPreview
     * @param  array<string, mixed>  $summary
     */
    public function __construct(
        public string $module,
        public int $loadId,
        public string $reference,
        public string $status,
        public ?string $periodKey,
        public int $version,
        public int $totalRows,
        public int $validRows,
        public int $processedRows,
        public int $errorRows,
        public int $emptyRows,
        public int $duplicateRows,
        public float $totalAmount,
        public int $itemCount,
        public array $errorPreview = [],
        public array $summary = [],
    ) {}

    public function hasErrors(): bool
    {
        return $this->errorRows > 0;
    }
}
