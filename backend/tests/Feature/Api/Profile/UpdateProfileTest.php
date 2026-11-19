<?php

namespace Tests\Feature\Api\Profile;

use App\Models\Language;
use App\Models\User;
use Database\Seeders\LanguageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class UpdateProfileTest extends TestCase
{
    use RefreshDatabase;

    // ------------------------------------------------------------------
    // PATCH /me: basic fields
    // ------------------------------------------------------------------

    /** PATCH /me answers with my profile in the OwnUser shape, like GET /me. */
    public function test_update_returns_own_user_shape(): void
    {
        $user = User::factory()->create();

        $response = $this->withToken($user->createToken('test')->plainTextToken)->patchJson('/api/me', [
            'username' => 'Ane',
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.username', 'Ane');
        $response->assertJsonPath('data.role', 'user');
        $response->assertJsonMissingPath('data.status');
    }

    /** Fields that are not sent keep their value. */
    public function test_update_only_changes_the_fields_sent(): void
    {
        $user = User::factory()->create([
            'username' => 'Ane',
            'description' => 'Lectora de novela.',
            'postal_code' => '01005',
        ]);

        $response = $this->withToken($user->createToken('test')->plainTextToken)->patchJson('/api/me', [
            'description' => 'Lectora de ensayo.',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'username' => 'Ane',
            'description' => 'Lectora de ensayo.',
            'postal_code' => '01005',
        ]);
    }

    /** 1 and 61 characters are rejected; 2 and 60 are accepted. */
    public function test_username_must_be_between_2_and_60_characters(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)->patchJson('/api/me', ['username' => 'A'])
            ->assertUnprocessable()->assertJsonValidationErrors('username');
        $this->withToken($token)->patchJson('/api/me', ['username' => str_repeat('a', 61)])
            ->assertUnprocessable()->assertJsonValidationErrors('username');

        $this->withToken($token)->patchJson('/api/me', ['username' => 'An'])->assertOk();
        $this->withToken($token)->patchJson('/api/me', ['username' => str_repeat('a', 60)])->assertOk();
    }

    /** 300 characters is the limit for the description. */
    public function test_description_cannot_be_longer_than_300_characters(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)->patchJson('/api/me', ['description' => str_repeat('a', 301)])
            ->assertUnprocessable()->assertJsonValidationErrors('description');

        $this->withToken($token)->patchJson('/api/me', ['description' => str_repeat('a', 300)])->assertOk();
    }

    /** A Spanish postal code has exactly 5 digits. */
    public function test_postal_code_must_have_5_digits(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        foreach (['0100', '010050', 'ABCDE'] as $postalCode) {
            $this->withToken($token)->patchJson('/api/me', ['postal_code' => $postalCode])
                ->assertUnprocessable()->assertJsonValidationErrors('postal_code');
        }
    }

    /** "01005" is stored as text, so the leading 0 is not lost. */
    public function test_postal_code_keeps_the_leading_zero(): void
    {
        // A location is sent too, so Google Maps is not needed here
        Http::preventStrayRequests();
        $user = User::factory()->create();

        $response = $this->withToken($user->createToken('test')->plainTextToken)->patchJson('/api/me', [
            'postal_code' => '01005',
            'location' => ['latitude' => 42.85, 'longitude' => -2.67],
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.postal_code', '01005');
        $this->assertSame('01005', $user->fresh()->postal_code);
    }

    /** status, strikes and the role are not part of the request: sending them changes nothing. */
    public function test_status_strikes_and_role_cannot_be_changed(): void
    {
        $user = User::factory()->create();

        $response = $this->withToken($user->createToken('test')->plainTextToken)->patchJson('/api/me', [
            'username' => 'Ane',
            'status' => 'suspended',
            'strikes' => 3,
            'role' => 'admin',
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.role', 'user');
        $user->refresh();
        $this->assertSame('Ane', $user->username);
        $this->assertSame('active', $user->status);
        $this->assertSame(0, $user->strikes);
        $this->assertSame('user', $user->profile->code);
    }

    public function test_update_requires_a_token(): void
    {
        $this->patchJson('/api/me', ['username' => 'Ane'])->assertUnauthorized();
    }

    // ------------------------------------------------------------------
    // PATCH /me: reading languages
    // ------------------------------------------------------------------

    /** A code that is not in the languages table is rejected, and the old list stays. */
    public function test_languages_with_an_unknown_code_return_422(): void
    {
        $this->seed(LanguageSeeder::class);
        $user = User::factory()->create();
        $user->languages()->attach(Language::where('code', 'es')->value('id'));

        $response = $this->withToken($user->createToken('test')->plainTextToken)->patchJson('/api/me', [
            'languages' => ['eu', 'xx'],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('languages.1');
        $this->assertEquals(['es'], $user->fresh()->languages->pluck('code')->all());
    }

    /** Valid codes replace the previous list (they are not added to it). */
    public function test_languages_replace_the_previous_list(): void
    {
        $this->seed(LanguageSeeder::class);
        $user = User::factory()->create();
        $user->languages()->attach(Language::whereIn('code', ['es', 'eu'])->pluck('id'));

        $response = $this->withToken($user->createToken('test')->plainTextToken)->patchJson('/api/me', [
            'languages' => ['en', 'fr'],
        ]);

        $response->assertOk();
        // Order is not guaranteed, so compare without it
        $this->assertEqualsCanonicalizing(['en', 'fr'], $response->json('data.languages'));
        $this->assertEqualsCanonicalizing(['en', 'fr'], $user->fresh()->languages->pluck('code')->all());
    }
}
