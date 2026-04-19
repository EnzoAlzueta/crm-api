<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Laravel\Sanctum\Sanctum;

abstract class TestCase extends BaseTestCase
{
    protected function tearDown(): void
    {
        if ($this->app?->bound('auth')) {
            $this->app['auth']->forgetGuards();
        }

        if (method_exists($this, 'flushHeaders')) {
            $this->flushHeaders();
        }

        parent::tearDown();
    }

    /**
     * Authenticate API requests as the given user (or a new one) via Sanctum.
     */
    protected function authenticateSanctum(?User $user = null): User
    {
        $user ??= User::factory()->create();
        Sanctum::actingAs($user, ['*']);

        return $user;
    }
}
