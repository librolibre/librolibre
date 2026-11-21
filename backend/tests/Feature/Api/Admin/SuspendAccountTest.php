<?php

namespace Tests\Feature\Api\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * POST /admin/users/{user}/suspend and /reactivate (#36, RN-17, RN-19, CU-18).
 * Both need an admin token (User::factory()->admin()) and a reason.
 *
 * Already covered elsewhere: a suspended user who logs in again is sent to /?error=suspended (GoogleLoginTest).
 *
 * Still to add in Sprint 3 (when books can be hidden by an admin):
 * - suspending moves the user's visible books to hidden
 * - reactivating makes available again only the books hidden by the suspension (CU-18, 5a)
 * Still to add in Sprint 4 (when offers, reports and notifications exist):
 * - suspending cancels the user's ongoing offers and notifies the other side (RN-17)
 * - suspending and reactivating notify the affected users
 * - reactivating keeps the history of reports and strikes (RN-26)
 */
class SuspendAccountTest extends TestCase
{
    use RefreshDatabase;

    // ------------------------------------------------------------------
    // Suspend
    // ------------------------------------------------------------------

    /** Without a reason, suspending returns 422 and the user stays active. */
    public function test_suspend_requires_a_reason(): void
    {
        $admin = User::factory()->admin()->create();
        $token = $admin->createToken('admin')->plainTextToken;

        $user = User::factory()->create();

        $bodies = [[], ['reason' => '       ']];

        foreach ($bodies as $body) {
            $response = $this->withToken($token)->postJson('/api/admin/users/'.$user->id.'/suspend', $body);

            $response->assertUnprocessable();
            $response->assertJsonValidationErrors('reason');
        }

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'status' => 'active',
        ]);

        $this->assertDatabaseCount('admin_actions', 0);
    }

    /** Suspending sets status to suspended and answers 200 with the user. */
    public function test_suspend_sets_status_suspended(): void
    {
        $admin = User::factory()->admin()->create();
        $token = $admin->createToken('admin')->plainTextToken;

        $user = User::factory()->create();

        $response = $this->withToken($token)->postJson('/api/admin/users/'.$user->id.'/suspend', ['reason' => 'No reason']);

        $response->assertOk();
        $response->assertJsonPath('data.status', 'suspended');
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'status' => 'suspended',
        ]);
    }

    /** All the suspended user's tokens are revoked: their next request gets 401. */
    public function test_suspend_revokes_all_tokens(): void
    {
        // Arrange: the user is logged in on two devices
        $admin = User::factory()->admin()->create();
        $adminToken = $admin->createToken('admin')->plainTextToken;

        $user = User::factory()->create();
        $officeToken = $user->createToken('office')->plainTextToken;
        $homeToken = $user->createToken('home')->plainTextToken;

        // Act
        $this->withToken($adminToken)
            ->postJson('/api/admin/users/'.$user->id.'/suspend', ['reason' => 'Spam'])
            ->assertOk();

        // Assert: neither of their tokens works any more
        $this->app['auth']->forgetGuards();
        $this->withToken($officeToken)->getJson('/api/me')->assertUnauthorized();

        $this->app['auth']->forgetGuards();
        $this->withToken($homeToken)->getJson('/api/me')->assertUnauthorized();

        // Only the suspended user's tokens are deleted: the admin's session goes on
        $this->assertDatabaseMissing('personal_access_tokens', ['tokenable_id' => $user->id]);
        $this->assertDatabaseHas('personal_access_tokens', ['tokenable_id' => $admin->id]);
    }

    /** Each suspension creates an admin_actions row with the admin, the action, the user and the reason. */
    public function test_suspend_is_logged_in_admin_actions(): void
    {
        $admin = User::factory()->admin()->create();
        $token = $admin->createToken('admin')->plainTextToken;
        $user = User::factory()->create();

        $this->withToken($token)
            ->postJson('/api/admin/users/'.$user->id.'/suspend', ['reason' => 'Insultos en el chat'])
            ->assertOk();

        $this->assertDatabaseCount('admin_actions', 1);
        $this->assertDatabaseHas('admin_actions', [
            'admin_id' => $admin->id,
            'action' => 'suspend_user',
            'target_type' => 'user',
            'target_id' => $user->id,
            'reason' => 'Insultos en el chat',
        ]);
    }

    /** An unknown user id returns 404. */
    public function test_suspending_an_unknown_user_returns_404(): void
    {
        $admin = User::factory()->admin()->create();
        $token = $admin->createToken('admin')->plainTextToken;

        $userId = 100000000000;

        $response = $this->withToken($token)->postJson('/api/admin/users/'.$userId.'/suspend', ['reason' => 'No reason']);

        $response->assertNotFound();
    }

    // ------------------------------------------------------------------
    // Suspend: business rules (409)
    // ------------------------------------------------------------------

    /** An admin cannot be suspended: 409 with code ADMIN_PROTECTED (RN-19). */
    public function test_suspending_an_admin_returns_409_admin_protected(): void
    {
        $admin = User::factory()->admin()->create();
        $token = $admin->createToken('admin')->plainTextToken;

        $otherAdmin = User::factory()->admin()->create();

        $response = $this->withToken($token)->postJson('/api/admin/users/'.$otherAdmin->id.'/suspend', ['reason' => 'No reason']);

        // Assert: 409 with the contract's shape (BusinessRuleError)
        $response->assertStatus(409);
        $response->assertJsonPath('code', 'ADMIN_PROTECTED');
        $response->assertJsonStructure(['message', 'code']);

        $this->assertDatabaseHas('users', [
            'id' => $otherAdmin->id,
            'status' => 'active',
        ]);
        $this->assertDatabaseCount('admin_actions', 0);
    }

    /** An admin cannot suspend themselves: 409 with code ADMIN_PROTECTED (RN-19). */
    public function test_suspending_yourself_returns_409_admin_protected(): void
    {
        $admin = User::factory()->admin()->create();
        $token = $admin->createToken('admin')->plainTextToken;

        $response = $this->withToken($token)->postJson('/api/admin/users/'.$admin->id.'/suspend', ['reason' => 'No reason']);

        $response->assertStatus(409);
        $response->assertJsonPath('code', 'ADMIN_PROTECTED');
        $response->assertJsonStructure(['message', 'code']);

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('personal_access_tokens', ['tokenable_id' => $admin->id]);
        $this->assertDatabaseCount('admin_actions', 0);
    }

    /** A user who is already suspended: 409 with code INVALID_STATUS. */
    public function test_suspending_a_suspended_user_returns_409_invalid_status(): void
    {
        // Arrange: the user is already suspended
        $admin = User::factory()->admin()->create();
        $token = $admin->createToken('admin')->plainTextToken;

        $user = User::factory()->create(['status' => 'suspended']);

        // Act
        $response = $this->withToken($token)->postJson('/api/admin/users/'.$user->id.'/suspend', ['reason' => 'Spam']);

        // Assert: 409 with the contract's shape (BusinessRuleError)
        $response->assertStatus(409);
        $response->assertJsonPath('code', 'INVALID_STATUS');
        $response->assertJsonStructure(['message', 'code']);

        // Nothing changed: still suspended, and no second action recorded
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'status' => 'suspended',
        ]);
        $this->assertDatabaseCount('admin_actions', 0);
    }

    // ------------------------------------------------------------------
    // Reactivate
    // ------------------------------------------------------------------

    /** Without a reason, reactivating returns 422 (the contract asks for a reason on both actions). */
    public function test_reactivate_requires_a_reason(): void {}

    /** Reactivating sets status to active and strikes to 0. */
    public function test_reactivate_sets_status_active_and_strikes_to_zero(): void {}

    /** Each reactivation creates an admin_actions row too. */
    public function test_reactivate_is_logged_in_admin_actions(): void {}

    /** Reactivating a user who is not suspended: 409 with code INVALID_STATUS (same rule as suspend). */
    public function test_reactivating_an_active_user_returns_409_invalid_status(): void {}
}
