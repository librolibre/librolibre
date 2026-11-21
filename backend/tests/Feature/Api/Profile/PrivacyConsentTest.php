<?php

namespace Tests\Feature\Api\Profile;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrivacyConsentTest extends TestCase
{
    use RefreshDatabase;

    /** Accepting the privacy policy answers 204 with no body. */
    public function test_privacy_consent_returns_204(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('google-login')->plainTextToken;

        $response = $this->withToken($token)->postJson('/api/me/privacy-consent');

        $response->assertNoContent();
    }

    /** The date and time of the consent are saved in privacy_accepted_at. */
    public function test_privacy_consent_stores_the_date_and_time(): void
    {
        $this->freezeSecond();
        $user = User::factory()->create();
        $token = $user->createToken('google-login')->plainTextToken;

        $this->withToken($token)->postJson('/api/me/privacy-consent');
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'privacy_accepted_at' => now()
        ]);
    }

    /** After accepting, GET /me says privacy_accepted: true, so the front end stops asking. */
    public function test_me_shows_privacy_accepted_after_consent(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('google-login')->plainTextToken;

        $consent = $this->withToken($token)->postJson('/api/me/privacy-consent');

        $this->app['auth']->forgetGuards();

        $response = $this->withToken($token)->getJson('/api/me');

        $response->assertJsonPath('data.privacy_accepted', true);
        $consent->assertNoContent();
    }

    /** Only a logged-in user can accept the policy. */
    public function test_privacy_consent_requires_a_token(): void
    {
        $this->postJson('/api/me/privacy-consent')->assertUnauthorized();
    }

    /** The date proves when consent was first given, so accepting again does not change it. */
    public function test_accepting_again_keeps_the_first_date(): void
    {
        $this->freezeSecond();
        $firstDate = now()->subDays(10);
        $user = User::factory()->create(['privacy_accepted_at' => $firstDate]);
        $token = $user->createToken('google-login')->plainTextToken;

        $this->withToken($token)->postJson('/api/me/privacy-consent')->assertNoContent();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'privacy_accepted_at' => $firstDate,
        ]);
    }
}
