<?php

namespace Tests\Feature\Api\Auth;

use App\Models\Profile;
use App\Models\User;
use Database\Seeders\ProfileSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
// use Illuminate\Support\Facades\Route;
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
    {}

    public function test_callback_redirects_with_error_when_user_cancels(): void
    {}

    public function test_suspended_user_is_redirected_with_error(): void
    {}

    public function test_code_can_be_exchanged_for_token(): void
    {}

    public function test_code_can_only_be_used_once(): void
    {}

    public function test_code_expires_after_60_seconds(): void
    {}

    public function test_token_expires_after_30_days(): void
    {}
}
