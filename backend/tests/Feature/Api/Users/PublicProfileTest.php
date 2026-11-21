<?php

namespace Tests\Feature\Api\Users;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicProfileTest extends TestCase
{
    use RefreshDatabase;

    // ------------------------------------------------------------------
    // GET /users/{user}: visitors and logged-in users (auth.optional)
    // ------------------------------------------------------------------

    /** A visitor (no token) sees the real profile of the user in the URL. */
    public function test_visitor_can_see_a_public_profile(): void
    {
        $user = User::factory()->create(['username' => 'Ane']);

        $response = $this->getJson('/api/users/'.$user->id);

        $response->assertOk();
        $response->assertJsonPath('data.id', $user->id);
        $response->assertJsonPath('data.username', 'Ane');
    }

    public function test_logged_in_user_can_see_a_public_profile(): void
    {
        $ane = User::factory()->create(['username' => 'Ane']);
        $viewer = User::factory()->create(['username' => 'Viewer']);

        $token = $viewer->createToken('viewer')->plainTextToken;

        $response = $this->withToken($token)->getJson('/api/users/'.$ane->id);

        $response->assertOk();
        $response->assertJsonPath('data.id', $ane->id);
        $response->assertJsonPath('data.username', 'Ane');
    }

    public function test_expired_token_is_treated_as_a_visitor(): void
    {
        $ane = User::factory()->create(['username' => 'Ane']);
        $viewer = User::factory()->create(['username' => 'Viewer']);

        $token = $viewer->createToken('viewer')->plainTextToken;

        $this->travel(31)->days();

        $response = $this->withToken($token)->getJson('/api/users/'.$ane->id);

        $response->assertOk();
        $response->assertJsonPath('data.id', $ane->id);
        $response->assertJsonPath('data.username', 'Ane');

    }

    // ------------------------------------------------------------------
    // What the public profile shows
    // ------------------------------------------------------------------

    public function test_public_profile_never_includes_private_fields(): void
    {
        $ane = User::factory()->create([
            'username' => 'Ane',
            'email' => 'ane@example.com',
            'postal_code' => '01005',
            'latitude' => 42.85,
            'longitude' => -2.67,
            'privacy_accepted_at' => now(),
        ]);

        $privateFields = [
            'profile_id', 'email', 'postal_code', 'location', 'latitude',
            'longitude', 'status', 'strikes', 'role', 'privacy_accepted',
            'privacy_accepted_at', 'google_id'
        ];

        $viewer = User::factory()->create(['username' => 'Viewer']);
        $token = $viewer->createToken('viewer')->plainTextToken;

        $response = $this->withToken($token)->getJson('/api/users/'.$ane->id);

        $response->assertOk();
        foreach ($privateFields as $field) {
            $response->assertJsonMissingPath('data.'.$field);
        }
    }

    /** blocked_by_me is missing for visitors, true for whoever blocked the user and false for anyone else. */
    public function test_blocked_by_me_only_appears_with_a_token(): void
    {
        // Arrange
        $ane = User::factory()->create(['username' => 'Ane']);

        $blocker = User::factory()->create(['username' => 'Blocker']);
        $blockerToken = $blocker->createToken('viewer')->plainTextToken;
        $blocker->blockedUsers()->attach($ane);

        $other = User::factory()->create(['username' => 'Other']);
        $otherToken = $other->createToken('viewer')->plainTextToken;

        // Act
        $visitorResponse = $this->getJson('/api/users/'.$ane->id);

        $blockerResponse = $this->withToken($blockerToken)->getJson('/api/users/'.$ane->id);

        $this->app['auth']->forgetGuards();

        $otherResponse = $this->withToken($otherToken)->getJson('/api/users/'.$ane->id);

        // Assert
        $visitorResponse->assertOk();
        $visitorResponse->assertJsonMissingPath('data.blocked_by_me');

        $blockerResponse->assertOk();
        $blockerResponse->assertJsonPath('data.blocked_by_me', true);

        $otherResponse->assertOk();
        $otherResponse->assertJsonPath('data.blocked_by_me', false);
    }

    // ------------------------------------------------------------------
    // When the profile is not visible
    // ------------------------------------------------------------------

    /** An id that nobody has returns 404 (route model binding finds no user). */
    public function test_unknown_user_returns_404(): void
    {
        $response = $this->getJson('/api/users/1000000');

        $response->assertNotFound();
        $response->assertJsonStructure(['message']);
    }

    /** Letters in the id return 404 too: Route::pattern only accepts numbers, so the route does not match. */
    public function test_non_numeric_user_id_returns_404(): void
    {
        $response = $this->getJson('/api/users/abcdefg');

        $response->assertNotFound();
        $response->assertJsonStructure(['message']);
    }

    /** A deleted account keeps its row (anonymised, #29), but its profile is not visible. */
    public function test_deleted_user_returns_404(): void
    {
        $deleted = User::factory()->create([
            'username' => 'usuario-eliminado',
            'status' => 'deleted',
        ]);

        $response = $this->getJson('/api/users/'.$deleted->id);

        $response->assertNotFound();
        $response->assertJsonStructure(['message']);
    }

    /**
     * Whoever was blocked gets 404 on the blocker's profile; everyone else still sees it.
     * Only one direction (#73): the blocker can still see the other profile, with blocked_by_me: true (test 5).
     */
    public function test_blocked_user_cannot_see_the_profile_of_who_blocked_them(): void
    {
        // Arrange
        $ane = User::factory()->create(['username' => 'Ane']);

        $viewer = User::factory()->create(['username' => 'Other']);
        $viewerToken = $viewer->createToken('viewer')->plainTextToken;
        $ane->blockedUsers()->attach($viewer);

        // Act
        $visitorResponse = $this->getJson('/api/users/'.$ane->id);

        $viewerResponse = $this->withToken($viewerToken)->getJson('/api/users/'.$ane->id);

        // Assert
        $visitorResponse->assertOk();

        $viewerResponse->assertNotFound();
        $viewerResponse->assertJsonStructure(['message']);
    }
}
