<?php

namespace App\Data;

readonly class BudgetFiltersData
{
    public function __construct(
        public array $periods = [],
        public array $clients = [],
        public array $regionals = [],
        public array $channels = [],
        public array $sellers = [],
        public array $transactionTypes = [],
        public array $categories = [],
        public ?string $dateFrom = null,
        public ?string $dateTo = null,
        public string $dateField = 'application_date',
    ) {}

    public static function fromArray(array $data): self
    {
        $dateField = (string) ($data['date_field'] ?? 'application_date');
        if (! in_array($dateField, ['application_date', 'invoice_date', 'due_date'], true)) {
            $dateField = 'application_date';
        }

        return new self(
            periods: array_values(array_filter((array) ($data['periods'] ?? []))),
            clients: array_values(array_filter((array) ($data['clients'] ?? []))),
            regionals: array_values(array_filter((array) ($data['regionals'] ?? []))),
            channels: array_values(array_filter((array) ($data['channels'] ?? []))),
            sellers: array_values(array_filter((array) ($data['sellers'] ?? []))),
            transactionTypes: array_values(array_filter((array) ($data['transaction_types'] ?? []))),
            categories: array_values(array_filter((array) ($data['categories'] ?? []))),
            dateFrom: $data['date_from'] ?? null,
            dateTo: $data['date_to'] ?? null,
            dateField: $dateField,
        );
    }
}
