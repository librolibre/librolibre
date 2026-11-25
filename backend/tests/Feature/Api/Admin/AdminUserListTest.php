<?php

namespace Tests\Feature\Api\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * GET /admin/users (#34): the admin panel's user list.
 * ?q= searches by username or email, ignoring upper/lower case (ilike in PostgreSQL).
 */
class AdminUserListTest extends TestCase
{
    use RefreshDatabase;

    /** ?q= finds users by username without caring about upper/lower case: «ane» and «ANE» find «Ane». */
    public function test_search_by_username_ignores_case(): void
    {
        // Arrange: the admin, Ane, and someone who must not match
        // Fixed emails: the factory's random ones could contain «an» and match the search by chance
        $admin = User::factory()->admin()->create(['username' => 'Admin', 'email' => 'admin@example.com']);
        $token = $admin->createToken('admin')->plainTextToken;

        $ane = User::factory()->create(['username' => 'Ane', 'email' => 'ane@example.com']);
        User::factory()->create(['username' => 'Mikel', 'email' => 'mikel@example.com']);

        foreach (['ane', 'ANE', 'aN'] as $search) {
            // Act
            $response = $this->withToken($token)->getJson('/api/admin/users?q='.$search);

            // Assert: only Ane, whatever the case of the search
            $response->assertOk();
            $this->assertSame([$ane->id], $response->json('data.*.id'), "Search «{$search}» should only find Ane");
        }
    }

    /** ?q= also searches the email, without caring about upper/lower case. */
    public function test_search_by_email_ignores_case(): void
    {
        $admin = User::factory()->admin()->create(['username' => 'Admin', 'email' => 'admin@example.com']);
        $token = $admin->createToken('admin')->plainTextToken;

        $ane = User::factory()->create(['username' => 'Ane', 'email' => 'ane.zubiri@example.com']);
        User::factory()->create(['username' => 'Mikel', 'email' => 'mikel@example.com']);

        $response = $this->withToken($token)->getJson('/api/admin/users?q=ZUBIRI');

        $response->assertOk();
        $this->assertSame([$ane->id], $response->json('data.*.id'));
    }
}
