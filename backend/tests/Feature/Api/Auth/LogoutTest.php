<?php

namespace Tests\Feature\Api\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_logout_returns_204(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('google-login')->plainTextToken;

        $response = $this->withToken($token)->postJson('/api/auth/logout');

        $response->assertNoContent();
    }

    public function test_token_no_longer_works_after_logout(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('google-login')->plainTextToken;

        $this->withToken($token)->postJson('/api/auth/logout')->assertNoContent();

        $this->app['auth']->forgetGuards();

        $this->withToken($token)->getJson('/api/me')->assertUnauthorized();
    }

    public function test_logout_only_revokes_the_current_token(): void
    {
        $user = User::factory()->create();
        // Different names, so we can check which token was deleted!
        $officeToken = $user->createToken('office')->plainTextToken;
        $homeToken = $user->createToken('home')->plainTextToken;

        $this->withToken($officeToken)->postJson('/api/auth/logout')->assertNoContent();
        $this->app['auth']->forgetGuards();

        $this->withToken($homeToken)->getJson('/api/me')->assertOk();

        $this->assertDatabaseCount('personal_access_tokens', 1);
        $this->assertDatabaseHas('personal_access_tokens', ['name' => 'home']);
        $this->assertDatabaseMissing('personal_access_tokens', ['name' => 'office']);
    }

    public function test_logout_requires_a_token(): void
    {
        $this->postJson('/api/auth/logout')->assertUnauthorized();
    }
}
