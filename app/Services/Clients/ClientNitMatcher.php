<?php

namespace App\Services\Clients;

use App\Models\Client;
use App\Services\Loads\Support\ImportNormalizer;
use Illuminate\Support\Str;

class ClientNitMatcher
{
    public function __construct(
        private readonly ImportNormalizer $normalizer,
    ) {}

    /**
     * @return array<int, string>
     */
    public function variants(?string $nit): array
    {
        if ($nit === null || trim($nit) === '') {
            return [];
        }

        $normalized = Str::upper(trim($nit));
        $variants = [$normalized];

        if (str_contains($normalized, '-')) {
            $base = Str::before($normalized, '-');
            if ($base !== '') {
                $variants[] = $base;
            }
        }

        return array_values(array_unique($variants));
    }

    public function find(?string $nit): ?Client
    {
        foreach ($this->variants($nit) as $variant) {
            $client = Client::query()->where('document_number', $variant)->first();

            if ($client) {
                return $client;
            }

            $client = Client::query()->where('code', $variant)->first();

            if ($client) {
                return $client;
            }
        }

        return null;
    }

    public function findByName(?string $name): ?Client
    {
        $label = $this->normalizer->normalizeText($name, 255);

        if ($label === null) {
            return null;
        }

        $client = Client::query()
            ->whereRaw('UPPER(TRIM(name)) = ?', [mb_strtoupper($label)])
            ->first();

        if ($client) {
            return $client;
        }

        $firstWord = Str::of($label)
            ->upper()
            ->ascii()
            ->replaceMatches('/[^A-Z0-9]+/', ' ')
            ->trim()
            ->explode(' ')
            ->first();

        if ($firstWord === null || strlen($firstWord) < 4) {
            return null;
        }

        return Client::query()
            ->whereRaw('UPPER(name) LIKE ?', ['%' . $firstWord . '%'])
            ->limit(100)
            ->get()
            ->first(fn (Client $candidate): bool => $this->normalizer->namesMatch($label, $candidate->name));
    }

    public function isPlaceholderIdentifier(?string $value): bool
    {
        if ($value === null || $value === '') {
            return false;
        }

        return str_starts_with($value, 'REC-')
            || str_starts_with($value, 'IMP-');
    }

    public function canAssignDocumentNumber(Client $client, string $masterNit): bool
    {
        if ($client->document_number === $masterNit) {
            return false;
        }

        if (! $this->isPlaceholderIdentifier($client->document_number)) {
            return false;
        }

        return ! Client::query()
            ->where('document_number', $masterNit)
            ->whereKeyNot($client->getKey())
            ->exists();
    }
}
