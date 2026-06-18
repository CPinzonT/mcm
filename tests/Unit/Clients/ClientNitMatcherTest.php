<?php

namespace Tests\Unit\Clients;

use App\Models\Client;
use App\Services\Clients\ClientNitMatcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientNitMatcherTest extends TestCase
{
    use RefreshDatabase;

    public function test_finds_client_by_nit_with_or_without_verification_digit(): void
    {
        $client = Client::query()->create([
            'code' => '901276722',
            'name' => 'Distriyoli',
            'document_type' => 'NIT',
            'document_number' => '901276722',
            'active' => true,
        ]);

        $matcher = new ClientNitMatcher();

        $this->assertTrue($matcher->find('901276722-2')?->is($client) ?? false);
        $this->assertTrue($matcher->find('901276722')?->is($client) ?? false);
    }

    public function test_finds_client_when_nit_is_stored_in_code(): void
    {
        $client = Client::query()->create([
            'code' => '1096033706',
            'name' => 'Natalia Cardona',
            'document_type' => 'NIT',
            'document_number' => '1096033706',
            'active' => true,
        ]);

        $matcher = new ClientNitMatcher();

        $this->assertTrue($matcher->find('1096033706')?->is($client) ?? false);
    }
}
