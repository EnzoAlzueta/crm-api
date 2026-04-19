<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Client;
use App\Models\Contact;
use App\Models\Note;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotesAndActivitiesTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_and_list_note_for_own_client(): void
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

        $create = $this->postJson('/api/clients/'.$client->id.'/notes', [
            'body' => 'First note',
        ]);

        $create->assertCreated()
            ->assertJsonPath('data.body', 'First note')
            ->assertJsonPath('data.client_id', $client->id);

        $list = $this->getJson('/api/clients/'.$client->id.'/notes');

        $list->assertOk();
        $this->assertSame(1, $list->json('meta.total'));
    }

    public function test_notes_global_index_only_includes_own_clients_notes(): void
    {
        $user = $this->authenticateSanctum();
        $client = Client::create([
            'user_id' => $user->id,
            'name' => 'Mine',
            'email' => 'm@test.test',
            'phone' => null,
            'company' => null,
            'status' => 'lead',
        ]);
        Note::create([
            'client_id' => $client->id,
            'contact_id' => null,
            'user_id' => $user->id,
            'body' => 'Visible',
        ]);

        $other = User::factory()->create();
        $otherClient = Client::create([
            'user_id' => $other->id,
            'name' => 'Other',
            'email' => 'o@test.test',
            'phone' => null,
            'company' => null,
            'status' => 'lead',
        ]);
        Note::create([
            'client_id' => $otherClient->id,
            'contact_id' => null,
            'user_id' => $other->id,
            'body' => 'Hidden',
        ]);

        $response = $this->getJson('/api/notes');
        $response->assertOk();
        $this->assertSame(1, $response->json('meta.total'));
        $this->assertSame('Visible', $response->json('data.0.body'));
    }

    public function test_user_can_update_and_delete_own_note(): void
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
        $note = Note::create([
            'client_id' => $client->id,
            'contact_id' => null,
            'user_id' => $user->id,
            'body' => 'Original',
        ]);

        $this->patchJson('/api/notes/'.$note->id, ['body' => 'Edited'])
            ->assertOk()
            ->assertJsonPath('data.body', 'Edited');

        $this->deleteJson('/api/notes/'.$note->id)
            ->assertOk()
            ->assertJsonPath('message', 'Note deleted');

        $this->assertSoftDeleted('notes', ['id' => $note->id]);
    }

    public function test_user_cannot_create_note_for_foreign_client(): void
    {
        $owner = User::factory()->create();
        $other = $this->authenticateSanctum();
        $client = Client::create([
            'user_id' => $owner->id,
            'name' => 'Owned',
            'email' => 'o@test.test',
            'phone' => null,
            'company' => null,
            'status' => 'lead',
        ]);

        $this->postJson('/api/clients/'.$client->id.'/notes', [
            'body' => 'Hack',
        ])->assertForbidden();
    }

    public function test_note_rejects_contact_from_another_client(): void
    {
        $user = $this->authenticateSanctum();
        $clientA = Client::create([
            'user_id' => $user->id,
            'name' => 'A',
            'email' => 'a@test.test',
            'phone' => null,
            'company' => null,
            'status' => 'lead',
        ]);
        $clientB = Client::create([
            'user_id' => $user->id,
            'name' => 'B',
            'email' => 'b@test.test',
            'phone' => null,
            'company' => null,
            'status' => 'lead',
        ]);

        $contactB = Contact::create([
            'client_id' => $clientB->id,
            'name' => 'CB',
            'email' => null,
            'phone' => null,
            'position' => null,
        ]);

        $this->postJson('/api/clients/'.$clientA->id.'/notes', [
            'body' => 'Bad link',
            'contact_id' => $contactB->id,
        ])->assertStatus(422);
    }

    public function test_user_cannot_view_foreign_note(): void
    {
        $owner = User::factory()->create();
        $this->authenticateSanctum();
        $client = Client::create([
            'user_id' => $owner->id,
            'name' => 'Owned',
            'email' => 'o@test.test',
            'phone' => null,
            'company' => null,
            'status' => 'lead',
        ]);
        $note = Note::create([
            'client_id' => $client->id,
            'contact_id' => null,
            'user_id' => $owner->id,
            'body' => 'Secret',
        ]);

        $this->getJson('/api/notes/'.$note->id)->assertForbidden();
    }

    public function test_user_can_create_activity_with_defaults(): void
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

        $this->postJson('/api/clients/'.$client->id.'/activities', [
            'title' => 'Call back',
        ])
            ->assertCreated()
            ->assertJsonPath('data.title', 'Call back')
            ->assertJsonPath('data.type', 'task');
    }

    public function test_activities_global_index_is_scoped(): void
    {
        $user = $this->authenticateSanctum();
        $client = Client::create([
            'user_id' => $user->id,
            'name' => 'Mine',
            'email' => 'm@test.test',
            'phone' => null,
            'company' => null,
            'status' => 'lead',
        ]);
        Activity::create([
            'client_id' => $client->id,
            'contact_id' => null,
            'user_id' => $user->id,
            'type' => 'task',
            'title' => 'Mine',
            'body' => null,
            'due_at' => null,
            'completed_at' => null,
        ]);

        $other = User::factory()->create();
        $otherClient = Client::create([
            'user_id' => $other->id,
            'name' => 'Other',
            'email' => 'o@test.test',
            'phone' => null,
            'company' => null,
            'status' => 'lead',
        ]);
        Activity::create([
            'client_id' => $otherClient->id,
            'contact_id' => null,
            'user_id' => $other->id,
            'type' => 'task',
            'title' => 'Hidden',
            'body' => null,
            'due_at' => null,
            'completed_at' => null,
        ]);

        $response = $this->getJson('/api/activities');
        $response->assertOk();
        $this->assertSame(1, $response->json('meta.total'));
        $this->assertSame('Mine', $response->json('data.0.title'));
    }

    public function test_user_can_update_and_delete_own_activity(): void
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
        $activity = Activity::create([
            'client_id' => $client->id,
            'contact_id' => null,
            'user_id' => $user->id,
            'type' => 'call',
            'title' => 'Old',
            'body' => null,
            'due_at' => null,
            'completed_at' => null,
        ]);

        $this->patchJson('/api/activities/'.$activity->id, [
            'title' => 'New title',
            'type' => 'meeting',
        ])
            ->assertOk()
            ->assertJsonPath('data.title', 'New title')
            ->assertJsonPath('data.type', 'meeting');

        $this->deleteJson('/api/activities/'.$activity->id)
            ->assertOk()
            ->assertJsonPath('message', 'Activity deleted');

        $this->assertSoftDeleted('activities', ['id' => $activity->id]);
    }

    public function test_notes_index_searches_by_body(): void
    {
        $user = $this->authenticateSanctum();
        $client = Client::create(['user_id' => $user->id, 'name' => 'A', 'email' => 'a@t.test', 'phone' => null, 'company' => null, 'status' => 'lead']);
        Note::create(['client_id' => $client->id, 'contact_id' => null, 'user_id' => $user->id, 'body' => 'Follow up next week']);
        Note::create(['client_id' => $client->id, 'contact_id' => null, 'user_id' => $user->id, 'body' => 'Send proposal']);

        $response = $this->getJson('/api/notes?search=follow');

        $response->assertOk();
        $this->assertSame(1, $response->json('meta.total'));
        $this->assertStringContainsStringIgnoringCase('follow', $response->json('data.0.body'));
    }

    public function test_activities_index_filters_by_type(): void
    {
        $user = $this->authenticateSanctum();
        $client = Client::create(['user_id' => $user->id, 'name' => 'A', 'email' => 'a@t.test', 'phone' => null, 'company' => null, 'status' => 'lead']);
        Activity::create(['client_id' => $client->id, 'contact_id' => null, 'user_id' => $user->id, 'type' => 'call',  'title' => 'Phone call',   'body' => null, 'due_at' => null, 'completed_at' => null]);
        Activity::create(['client_id' => $client->id, 'contact_id' => null, 'user_id' => $user->id, 'type' => 'email', 'title' => 'Send email',   'body' => null, 'due_at' => null, 'completed_at' => null]);
        Activity::create(['client_id' => $client->id, 'contact_id' => null, 'user_id' => $user->id, 'type' => 'task',  'title' => 'Review docs',  'body' => null, 'due_at' => null, 'completed_at' => null]);

        $response = $this->getJson('/api/activities?type=call');

        $response->assertOk();
        $this->assertSame(1, $response->json('meta.total'));
        $this->assertSame('call', $response->json('data.0.type'));
    }

    public function test_activities_index_filters_by_status(): void
    {
        $user = $this->authenticateSanctum();
        $client = Client::create(['user_id' => $user->id, 'name' => 'A', 'email' => 'a@t.test', 'phone' => null, 'company' => null, 'status' => 'lead']);
        Activity::create(['client_id' => $client->id, 'contact_id' => null, 'user_id' => $user->id, 'type' => 'task', 'title' => 'Done task',    'body' => null, 'due_at' => null, 'completed_at' => now()]);
        Activity::create(['client_id' => $client->id, 'contact_id' => null, 'user_id' => $user->id, 'type' => 'task', 'title' => 'Pending task', 'body' => null, 'due_at' => null, 'completed_at' => null]);

        $done = $this->getJson('/api/activities?status=done');
        $done->assertOk();
        $this->assertSame(1, $done->json('meta.total'));
        $this->assertNotNull($done->json('data.0.completed_at'));

        $pending = $this->getJson('/api/activities?status=pending');
        $pending->assertOk();
        $this->assertSame(1, $pending->json('meta.total'));
        $this->assertNull($pending->json('data.0.completed_at'));
    }

    public function test_activities_index_searches_by_title(): void
    {
        $user = $this->authenticateSanctum();
        $client = Client::create(['user_id' => $user->id, 'name' => 'A', 'email' => 'a@t.test', 'phone' => null, 'company' => null, 'status' => 'lead']);
        Activity::create(['client_id' => $client->id, 'contact_id' => null, 'user_id' => $user->id, 'type' => 'call', 'title' => 'Renewal call', 'body' => null, 'due_at' => null, 'completed_at' => null]);
        Activity::create(['client_id' => $client->id, 'contact_id' => null, 'user_id' => $user->id, 'type' => 'task', 'title' => 'Prepare deck', 'body' => null, 'due_at' => null, 'completed_at' => null]);

        $response = $this->getJson('/api/activities?search=renewal');

        $response->assertOk();
        $this->assertSame(1, $response->json('meta.total'));
        $this->assertSame('Renewal call', $response->json('data.0.title'));
    }

    public function test_user_cannot_view_foreign_activity(): void
    {
        $owner = User::factory()->create();
        $this->authenticateSanctum();
        $client = Client::create([
            'user_id' => $owner->id,
            'name' => 'Owned',
            'email' => 'o@test.test',
            'phone' => null,
            'company' => null,
            'status' => 'lead',
        ]);
        $activity = Activity::create([
            'client_id' => $client->id,
            'contact_id' => null,
            'user_id' => $owner->id,
            'type' => 'call',
            'title' => 'Private call',
            'body' => null,
            'due_at' => null,
            'completed_at' => null,
        ]);

        $this->getJson('/api/activities/'.$activity->id)->assertForbidden();
    }
}
