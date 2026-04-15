<?php

namespace App\Data\Loads;

final readonly class LoadValidationErrorData
{
    public function __construct(
        public ?int $rowNumber,
        public ?string $field,
        public ?string $code,
        public string $message,
        public array $payload = [],
    ) {}

    public static function general(string $message, ?string $code = null, array $payload = []): self
    {
        return new self(
            rowNumber: null,
            field: null,
            code: $code,
            message: $message,
            payload: $payload,
        );
    }

    public function toArray(): array
    {
        return [
            'row_number' => $this->rowNumber,
            'field' => $this->field,
            'error_code' => $this->code,
            'message' => $this->message,
            'row_payload' => $this->payload ?: null,
        ];
    }
}
