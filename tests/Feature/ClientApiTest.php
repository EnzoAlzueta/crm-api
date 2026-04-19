<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_only_authenticated_users_clients(): void
    {
        $user = $this->authenticateSanctum();
        Client::create([
            'user_id' => $user->id,
            'name' => 'Mine',
            'email' => 'mine@test.test',
            'phone' => null,
            'company' => null,
            'status' => 'lead',
        ]);

        $other = User::factory()->create();
        Client::create([
            'user_id' => $other->id,
            'name' => 'Theirs',
            'email' => 'theirs@test.test',
            'phone' => null,
            'company' => null,
            'status' => 'lead',
        ]);

        $response = $this->getJson('/api/clients');

        $response->assertOk();
        $this->assertSame(1, $response->json('meta.total'));
        $this->assertSame('Mine', $response->json('data.0.name'));
    }

    public function test_store_assigns_authenticated_user(): void
    {
        $user = $this->authenticateSanctum();

        $response = $this->postJson('/api/clients', [
            'name' => 'New Corp',
            'email' => 'info@newcorp.test',
            'status' => 'lead',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'New Corp')
            ->assertJsonPath('data.user_id', $user->id);
    }

    public function test_show_update_destroy_own_client(): void
    {
        $user = $this->authenticateSanctum();
        $client = Client::create([
            'user_id' => $user->id,
            'name' => 'Acme',
            'email' => 'a@acme.test',
            'phone' => null,
            'company' => null,
            'status' => 'lead',
        ]);

        $this->getJson('/api/clients/'.$client->id)
            ->assertOk()
            ->assertJsonPath('data.name', 'Acme');

        $this->patchJson('/api/clients/'.$client->id, [
            'name' => 'Acme Updated',
        ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Acme Updated');

        $this->deleteJson('/api/clients/'.$client->id)
            ->assertOk()
            ->assertJsonPath('message', 'Client deleted');

        $this->assertSoftDeleted('clients', ['id' => $client->id]);
    }

    public function test_index_filters_by_status(): void
    {
        $user = $this->authenticateSanctum();
        Client::create(['user_id' => $user->id, 'name' => 'Active Co',   'email' => 'a@test.test', 'phone' => null, 'company' => null, 'status' => 'active']);
        Client::create(['user_id' => $user->id, 'name' => 'Lead Corp',   'email' => 'b@test.test', 'phone' => null, 'company' => null, 'status' => 'lead']);
        Client::create(['user_id' => $user->id, 'name' => 'Churned Ltd', 'email' => 'c@test.test', 'phone' => null, 'company' => null, 'status' => 'churned']);

        $response = $this->getJson('/api/clients?status=active');

        $response->assertOk();
        $this->assertSame(1, $response->json('meta.total'));
        $this->assertSame('Active Co', $response->json('data.0.name'));
    }

    public function test_index_searches_by_name(): void
    {
        $user = $this->authenticateSanctum();
        Client::create(['user_id' => $user->id, 'name' => 'Acme Corp',    'email' => 'a@acme.test',    'phone' => null, 'company' => null, 'status' => 'lead']);
        Client::create(['user_id' => $user->id, 'name' => 'Beta Systems', 'email' => 'b@beta.test',    'phone' => null, 'company' => null, 'status' => 'lead']);

        $response = $this->getJson('/api/clients?search=acme');

        $response->assertOk();
        $this->assertSame(1, $response->json('meta.total'));
        $this->assertSame('Acme Corp', $response->json('data.0.name'));
    }

    public function test_index_respects_per_page(): void
    {
        $user = $this->authenticateSanctum();
        for ($i = 1; $i <= 15; $i++) {
            Client::create(['user_id' => $user->id, 'name' => "Client $i", 'email' => "c$i@test.test", 'phone' => null, 'company' => null, 'status' => 'lead']);
        }

        $response = $this->getJson('/api/clients?per_page=5');

        $response->assertOk();
        $this->assertSame(15, $response->json('meta.total'));
        $this->assertCount(5, $response->json('data'));
    }

    public function test_cannot_modify_other_users_client(): void
    {
        $owner = User::factory()->create();
        $intruder = $this->authenticateSanctum();

        $client = Client::create([
            'user_id' => $owner->id,
            'name' => 'Protected',
            'email' => 'p@test.test',
            'phone' => null,
            'company' => null,
            'status' => 'lead',
        ]);

        $this->patchJson('/api/clients/'.$client->id, ['name' => 'Hacked'])
            ->assertForbidden();

        $this->deleteJson('/api/clients/'.$client->id)
            ->assertForbidden();
    }
}
