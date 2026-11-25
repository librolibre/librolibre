<?php

namespace Tests\Feature\Api\Admin;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Access to the /admin/* routes (#32): the "admin" Gate in AppServiceProvider,
 * used by the can:admin middleware on the admin route group.
 * Admins are created with User::factory()->admin().
 */
class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    /** A user with the admin profile gets 200 on GET /admin/users. */
    public function test_admin_can_access_admin_routes(): void
    {
        $admin = User::factory()->admin()->create();
        $token = $admin->createToken('admin')->plainTextToken;

        // The admin Gate (AppServiceProvider::boot) lets them through
        $this->withToken($token)->getJson('/api/admin/users')->assertOk();
    }

    /** A user with the user profile gets 403 on the admin routes. */
    public function test_regular_user_gets_403_on_admin_routes(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('user')->plainTextToken;

        // The admin Gate says no, so can:admin answers 403
        $this->withToken($token)->getJson('/api/admin/users')->assertForbidden();
    }

    /** Without a token, the admin routes answer 401 (auth:sanctum runs before can:admin). */
    public function test_admin_routes_require_a_token(): void
    {
        $this->getJson('/api/admin/users')->assertUnauthorized();
    }

    /** The role cannot be changed through the API: PATCH /me with profile_id or role changes nothing. */
    public function test_role_cannot_be_changed_through_patch_me(): void
    {
        $user = User::factory()->create();

        // First array: what to search by; second: what to fill in if it has to be created
        $adminProfile = Profile::firstOrCreate(['code' => 'admin'], ['name' => 'Administrador']);

        $response = $this->withToken($user->createToken('test')->plainTextToken)->patchJson('/api/me', [
            'username' => 'Ane',
            'role' => 'admin',
            'profile_id' => $adminProfile->id,
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.role', 'user');
        $user->refresh();

        $this->assertSame('Ane', $user->username);
        $this->assertSame('user', $user->profile->code);
    }
}
