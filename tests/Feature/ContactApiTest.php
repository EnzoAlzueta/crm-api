<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactApiTest extends TestCase
{
    use RefreshDatabase;

    private function makeClientFor(User $user, string $name = 'Client'): Client
    {
        return Client::create([
            'user_id' => $user->id,
            'name' => $name,
            'email' => strtolower($name).'@test.test',
            'phone' => null,
            'company' => null,
            'status' => 'lead',
        ]);
    }

    public function test_can_create_contact_under_own_client(): void
    {
        $user = $this->authenticateSanctum();
        $client = $this->makeClientFor($user);

        $response = $this->postJson('/api/clients/'.$client->id.'/contacts', [
            'name' => 'Ana',
            'email' => 'ana@test.test',
            'position' => 'CTO',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Ana')
            ->assertJsonPath('data.client_id', $client->id);
    }

    public function test_index_by_client_and_global_index_are_scoped(): void
    {
        $user = $this->authenticateSanctum();
        $client = $this->makeClientFor($user);
        Contact::create([
            'client_id' => $client->id,
            'name' => 'C1',
            'email' => null,
            'phone' => null,
            'position' => null,
        ]);

        $otherUser = User::factory()->create();
        $otherClient = $this->makeClientFor($otherUser, 'Other');
        Contact::create([
            'client_id' => $otherClient->id,
            'name' => 'Hidden',
            'email' => null,
            'phone' => null,
            'position' => null,
        ]);

        $byClient = $this->getJson('/api/clients/'.$client->id.'/contacts');
        $byClient->assertOk();
        $this->assertSame(1, $byClient->json('meta.total'));

        $all = $this->getJson('/api/contacts');
        $all->assertOk();
        $this->assertSame(1, $all->json('meta.total'));
        $this->assertSame('C1', $all->json('data.0.name'));
    }

    public function test_show_update_destroy_own_contact(): void
    {
        $user = $this->authenticateSanctum();
        $client = $this->makeClientFor($user);
        $contact = Contact::create([
            'client_id' => $client->id,
            'name' => 'Bob',
            'email' => null,
            'phone' => null,
            'position' => null,
        ]);

        $this->getJson('/api/contacts/'.$contact->id)
            ->assertOk()
            ->assertJsonPath('data.name', 'Bob');

        $this->patchJson('/api/contacts/'.$contact->id, ['name' => 'Robert'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Robert');

        $this->deleteJson('/api/contacts/'.$contact->id)
            ->assertOk()
            ->assertJsonPath('message', 'Contact deleted');

        $this->assertSoftDeleted('contacts', ['id' => $contact->id]);
    }

    public function test_index_searches_by_name_and_position(): void
    {
        $user = $this->authenticateSanctum();
        $client = $this->makeClientFor($user);
        Contact::create(['client_id' => $client->id, 'name' => 'Alice Engineer', 'email' => null,          'phone' => null, 'position' => 'CTO']);
        Contact::create(['client_id' => $client->id, 'name' => 'Bob Sales',      'email' => 'bob@t.test', 'phone' => null, 'position' => 'AE']);

        $byName = $this->getJson('/api/contacts?search=alice');
        $byName->assertOk();
        $this->assertSame(1, $byName->json('meta.total'));
        $this->assertSame('Alice Engineer', $byName->json('data.0.name'));

        $byPosition = $this->getJson('/api/contacts?search=CTO');
        $byPosition->assertOk();
        $this->assertSame(1, $byPosition->json('meta.total'));
    }

    public function test_cannot_create_contact_on_foreign_client(): void
    {
        $owner = User::factory()->create();
        $client = $this->makeClientFor($owner, 'Owned');
        $this->authenticateSanctum();

        $this->postJson('/api/clients/'.$client->id.'/contacts', [
            'name' => 'Intruder',
        ])->assertForbidden();
    }
}
