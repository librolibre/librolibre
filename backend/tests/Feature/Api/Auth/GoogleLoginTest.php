<?php

namespace Tests\Feature\Api\Auth;

use App\Models\User;
use Database\Seeders\ProfileSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Laravel\Socialite\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Tests\TestCase;

class GoogleLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_redirect_sends_user_to_google(): void
    {
        Socialite::fake('google');

        $response = $this->get('/api/auth/google/redirect');

        $response->assertRedirect('https://socialite.fake/google/authorize');
    }

    public function test_callback_creates_new_user_and_redirects_with_code(): void
    {
        $this->seed(ProfileSeeder::class);

        Socialite::fake('google', (new SocialiteUser)->map([
            'id' => '123456789',
            'email' => 'ane@example.com',
            'name' => 'Ane Igartiburu',
            'avatar' => 'https://photo_path'
        ])->setRaw(['given_name' => 'Ane']));

        $response = $this->get('/api/auth/google/callback');

        $response->assertRedirectContains(config('app.frontend_url').'/auth/callback?code=');
        $this->assertDatabaseHas('users', [
            'google_id' => '123456789',
            'email' => 'ane@example.com',
            'username' => 'Ane',
            'status' => 'active',
            'privacy_accepted_at' => null
        ]);
    }

    public function test_callback_finds_existing_user_by_google_id(): void
    {
        $this->seed(ProfileSeeder::class);

        $user = User::factory()->create(['google_id' => '123456789', 'username' => 'Ane la de los libros']);
        Socialite::fake('google', (new SocialiteUser)->map([
            'id' => '123456789',
            'email' => 'ane@example.com',
            'name' => 'Ane Igartiburu',
            'avatar' => 'https://photo_path'
        ])->setRaw(['given_name' => 'Ane']));

        $response = $this->get('/api/auth/google/callback');
        $response->assertRedirectContains(config('app.frontend_url').'/auth/callback?code=');

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'username' => 'Ane la de los libros',
        ]);
    }

    public function test_callback_redirects_with_error_when_user_cancels(): void
    {
        $this->seed(ProfileSeeder::class);

        Socialite::fake('google', (new SocialiteUser)->map([
            'id' => '123456789',
            'email' => 'ane@example.com',
            'name' => 'Ane Igartiburu',
            'avatar' => 'https://photo_path'
        ])->setRaw(['given_name' => 'Ane']));

        $response = $this->get('/api/auth/google/callback?error=access_denied');

        $response->assertRedirect(config('app.frontend_url').'/?error=cancelled');
        $this->assertDatabaseCount('users', 0);
    }

    public function test_suspended_user_is_redirected_with_error(): void
    {
        $this->seed(ProfileSeeder::class);

        $user = User::factory()->create(['google_id' => '123456789', 'status' => 'suspended']);
        Socialite::fake('google', (new SocialiteUser)->map([
            'id' => '123456789',
            'email' => 'ane@example.com',
            'name' => 'Ane Igartiburu',
            'avatar' => 'https://photo_path'
        ])->setRaw(['given_name' => 'Ane']));

        $response = $this->get('/api/auth/google/callback');

        $response->assertRedirect(config('app.frontend_url').'/?error=suspended');
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'status' => 'suspended',
        ]);
    }

    public function test_code_can_be_exchanged_for_token(): void
    {
        $user = User::factory()->create();

        $code = 'test-code';
        Cache::put('login-code:'.$code, $user->id, 60);

        $response = $this->postJson('/api/auth/token', ['code' => $code]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['token', 'is_new_user', 'user']);
        $response->assertJsonPath('is_new_user', true);
        $response->assertJsonPath('user.id', $user->id);
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'tokenable_type' => User::class
        ]);
    }

    /** The user in the token response has the same shape as GET /me (OwnUser), not the raw model. */
    public function test_token_response_returns_own_user_shape(): void
    {
        $user = User::factory()->create();
        Cache::put('login-code:test-code', $user->id, 60);

        $response = $this->postJson('/api/auth/token', ['code' => 'test-code']);

        $response->assertJsonPath('user.role', 'user');
        $response->assertJsonPath('user.privacy_accepted', false);
        $response->assertJsonMissingPath('user.status');
        $response->assertJsonMissingPath('user.data');
    }

    public function test_code_can_only_be_used_once(): void
    {
        $user = User::factory()->create();

        $code = 'test-code';
        Cache::put('login-code:'.$code, $user->id, 60);

        $this->postJson('/api/auth/token', ['code' => $code])->assertOk();
        $response = $this->postJson('/api/auth/token', ['code' => $code]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('code');
    }

    public function test_code_expires_after_60_seconds(): void
    {
        $user = User::factory()->create();

        $code = 'test-code';
        Cache::put('login-code:'.$code, $user->id, 60);

        $this->travel(61)->seconds();

        $response = $this->postJson('/api/auth/token', ['code' => $code]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('code');
    }

    public function test_token_expires_after_30_days(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('google-login')->plainTextToken;

        $this->withToken($token)->getJson('/api/me')->assertOk();

        // Laravel remembers the logged-in user between requests in a test
        $this->app['auth']->forgetGuards();
        $this->travel(31)->days();

        $this->withToken($token)->getJson('/api/me')->assertUnauthorized();
    }
}
