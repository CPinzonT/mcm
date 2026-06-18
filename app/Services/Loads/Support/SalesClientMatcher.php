<?php

namespace App\Services\Loads\Support;

use App\Models\Client;

class SalesClientMatcher
{
    /**
     * @param  array<string, mixed>  $row
     * @param  array<string, int|null>  $cache
     */
    public function resolveClientId(array $row, array &$cache = []): ?int
    {
        $cacheKey = implode('|', [
            (string) ($row['client_code'] ?? ''),
            (string) ($row['client_nit'] ?? ''),
            (string) ($row['client_name'] ?? ''),
        ]);

        if (array_key_exists($cacheKey, $cache)) {
            return $cache[$cacheKey];
        }

        $clientId = $this->lookupClientId($row);
        $cache[$cacheKey] = $clientId;

        return $clientId;
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function lookupClientId(array $row): ?int
    {
        $code = $this->normalizeCode($row['client_code'] ?? null);

        if ($code !== null) {
            $byCode = Client::query()->where('code', $code)->value('id');

            if ($byCode) {
                return (int) $byCode;
            }
        }

        $nit = $this->normalizeCode($row['client_nit'] ?? null);

        if ($nit !== null) {
            $byNit = Client::query()->where('document_number', $nit)->value('id');

            if ($byNit) {
                return (int) $byNit;
            }
        }

        $name = trim((string) ($row['client_name'] ?? ''));

        if ($name !== '') {
            $byName = Client::query()->where('name', $name)->value('id');

            if ($byName) {
                return (int) $byName;
            }
        }

        return null;
    }

    private function normalizeCode(mixed $value): ?string
    {
        $text = strtoupper(trim((string) ($value ?? '')));

        return $text !== '' ? $text : null;
    }
}
