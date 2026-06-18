<?php

namespace App\Services\Clients;

use App\Models\Client;
use Illuminate\Support\Str;

class ClientNitMatcher
{
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
}
