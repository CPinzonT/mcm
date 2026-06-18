<?php

namespace Tests\Unit\Clients;

use App\Models\Client;
use App\Services\Clients\ClientNitMatcher;
use App\Services\Loads\Support\ImportNormalizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientNitMatcherTest extends TestCase
{
    use RefreshDatabase;

    private ClientNitMatcher $matcher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->matcher = new ClientNitMatcher(new ImportNormalizer());
    }

    public function test_finds_client_by_nit_with_or_without_verification_digit(): void
    {
        $client = Client::query()->create([
            'code' => '901276722',
            'name' => 'Distriyoli',
            'document_type' => 'NIT',
            'document_number' => '901276722',
            'active' => true,
        ]);

        $this->assertTrue($this->matcher->find('901276722-2')?->is($client) ?? false);
        $this->assertTrue($this->matcher->find('901276722')?->is($client) ?? false);
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

        $this->assertTrue($this->matcher->find('1096033706')?->is($client) ?? false);
    }

    public function test_finds_client_by_name_when_nit_is_placeholder(): void
    {
        $client = Client::query()->create([
            'code' => 'REC-ff902226f4',
            'name' => 'ABASTECIMIENTOS & SERVICIOS SAS',
            'document_type' => 'NIT',
            'document_number' => 'REC-ff902226f4',
            'active' => true,
        ]);

        $found = $this->matcher->findByName('ABASTECIMIENTOS & SERVICIOS SAS');

        $this->assertNotNull($found);
        $this->assertTrue($found->is($client));
    }

    public function test_allows_replacing_placeholder_nit_with_master_nit(): void
    {
        $client = Client::query()->create([
            'code' => 'REC-ff902226f4',
            'name' => 'ABASTECIMIENTOS & SERVICIOS SAS',
            'document_type' => 'NIT',
            'document_number' => 'REC-ff902226f4',
            'active' => true,
        ]);

        $this->assertTrue($this->matcher->canAssignDocumentNumber($client, '901234567-8'));
        $this->assertTrue($this->matcher->isPlaceholderIdentifier($client->document_number));
    }
}
