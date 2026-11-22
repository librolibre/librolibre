<?php

namespace Tests\Feature\Console;

use App\Models\User;
use Database\Seeders\ProfileSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * php artisan user:role <email> <role> (#32, RN-19): the only way to change a role.
 * The API never changes it (PATCH /me ignores profile_id), so this runs on the server by hand.
 */
class UserRoleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // The roles are reference data: in production they come from ProfileSeeder too
        $this->seed(ProfileSeeder::class);
    }

    /** A regular user becomes an admin. */
    public function test_it_makes_a_user_admin(): void
    {
        $user = User::factory()->create(['email' => 'ane@example.com']);

        $this->artisan('user:role', ['email' => 'ane@example.com', 'role' => 'admin'])->assertSuccessful();

        $this->assertTrue($user->fresh()->isAdmin());
    }

    /** The email is found whatever its case (people type it by hand). */
    public function test_the_email_is_case_insensitive(): void
    {
        $user = User::factory()->create(['email' => 'ane@example.com']);

        $this->artisan('user:role', ['email' => 'Ane@Example.COM', 'role' => 'admin'])->assertSuccessful();

        $this->assertTrue($user->fresh()->isAdmin());
    }

    /** An admin goes back to being a regular user, as long as another admin is left. */
    public function test_it_makes_an_admin_a_regular_user(): void
    {
        User::factory()->admin()->create();
        $admin = User::factory()->admin()->create(['email' => 'iker@example.com']);

        $this->artisan('user:role', ['email' => 'iker@example.com', 'role' => 'user'])->assertSuccessful();

        $this->assertFalse($admin->fresh()->isAdmin());
    }

    /** The last admin cannot be demoted: nobody could reach the admin panel any more. */
    public function test_it_refuses_to_demote_the_last_admin(): void
    {
        $admin = User::factory()->admin()->create(['email' => 'iker@example.com']);

        $this->artisan('user:role', ['email' => 'iker@example.com', 'role' => 'user'])->assertFailed();

        $this->assertTrue($admin->fresh()->isAdmin());
    }

    /** An email that is not in the database fails and changes nothing. */
    public function test_it_fails_with_an_unknown_email(): void
    {
        $user = User::factory()->create();

        $this->artisan('user:role', ['email' => 'nobody@example.com', 'role' => 'admin'])->assertFailed();

        $this->assertFalse($user->fresh()->isAdmin());
    }

    /** A role that does not exist in profiles fails and changes nothing. */
    public function test_it_fails_with_an_unknown_role(): void
    {
        $user = User::factory()->create(['email' => 'ane@example.com']);
        $before = $user->profile_id;

        $this->artisan('user:role', ['email' => 'ane@example.com', 'role' => 'superadmin'])->assertFailed();

        $this->assertSame($before, $user->fresh()->profile_id);
    }

    /** A deleted (anonymised) account cannot get a role back. */
    public function test_it_ignores_deleted_accounts(): void
    {
        $user = User::factory()->create(['email' => 'ane@example.com']);
        $user->status = 'deleted';
        $user->save();

        $this->artisan('user:role', ['email' => 'ane@example.com', 'role' => 'admin'])->assertFailed();

        $this->assertFalse($user->fresh()->isAdmin());
    }
}
