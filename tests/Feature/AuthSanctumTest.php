<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthSanctumTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_and_receive_token(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'user' => ['id', 'name', 'email'],
                'token',
            ]);
    }

    public function test_user_can_login_and_receive_token(): void
    {
        $user = User::factory()->create([
            'email' => 'login@example.com',
            'password' => 'password123',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'user' => ['id', 'name', 'email'],
                'token',
            ]);
    }

    public function test_clients_route_requires_authentication(): void
    {
        $response = $this->getJson('/api/clients');

        $response->assertUnauthorized();
    }

    public function test_authenticated_user_can_fetch_auth_user(): void
    {
        $user = User::factory()->create(['email' => 'me@example.com']);
        $this->authenticateSanctum($user);

        $this->getJson('/api/auth/user')
            ->assertOk()
            ->assertJsonPath('data.email', 'me@example.com');
    }

    public function test_logout_revokes_current_token(): void
    {
        $user = User::factory()->create();
        $plainToken = $user->createToken('api')->plainTextToken;

        $this->assertSame(1, $user->tokens()->count());

        $this->withHeader('Authorization', 'Bearer '.$plainToken)
            ->postJson('/api/auth/logout')
            ->assertOk();

        $this->assertSame(0, $user->fresh()->tokens()->count());

        $this->app['auth']->forgetGuards();

        $this->withHeader('Authorization', 'Bearer '.$plainToken)
            ->getJson('/api/clients')
            ->assertUnauthorized();
    }

    public function test_user_cannot_access_other_users_client(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();

        $client = Client::create([
            'user_id' => $owner->id,
            'name' => 'Owner Client',
            'email' => 'owner-client@example.com',
            'phone' => '123456789',
            'company' => 'Owner Inc',
            'status' => 'lead',
        ]);

        $token = $intruder->createToken('api')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/clients/'.$client->id);

        $response->assertForbidden();
    }

    public function test_user_cannot_access_other_users_contact(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();

        $client = Client::create([
            'user_id' => $owner->id,
            'name' => 'Owner Client',
            'email' => 'owner-client@example.com',
            'phone' => '123456789',
            'company' => 'Owner Inc',
            'status' => 'lead',
        ]);

        $contact = Contact::create([
            'client_id' => $client->id,
            'name' => 'Owner Contact',
            'email' => 'owner-contact@example.com',
            'phone' => '7777777',
            'position' => 'Manager',
        ]);

        $token = $intruder->createToken('api')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/contacts/'.$contact->id);

        $response->assertForbidden();
    }
}
