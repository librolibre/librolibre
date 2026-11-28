<?php

namespace Tests\Feature\Api\Admin;

use App\Models\Book;
use App\Models\Rating;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
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

    /** books_count and average_rating come loaded with the list (withCount / withAvg) and are still correct. */
    public function test_books_count_and_average_rating_are_correct(): void
    {
        $admin = User::factory()->admin()->create();
        $token = $admin->createToken('admin')->plainTextToken;

        // Ane: 2 books and two ratings (4 and 5). Mikel: nothing.
        $ane = User::factory()->create();
        Book::factory()->count(2)->create(['user_id' => $ane->id]);
        Rating::factory()->create(['user_rated_id' => $ane->id, 'score' => 4]);
        Rating::factory()->create(['user_rated_id' => $ane->id, 'score' => 5]);
        $mikel = User::factory()->create();

        $users = collect($this->withToken($token)->getJson('/api/admin/users')->assertOk()->json('data'))
            ->keyBy('id');

        $this->assertSame(2, $users[$ane->id]['books_count']);
        $this->assertEquals(4.5, $users[$ane->id]['average_rating']);
        $this->assertSame(0, $users[$mikel->id]['books_count']);
        $this->assertNull($users[$mikel->id]['average_rating']);
    }

    /**
     * No N+1: the number of queries is the same with 2 users or with 15.
     * Before the fix every row made 2 queries of its own (avg + count): about 6 s per page against Neon.
     */
    public function test_number_of_queries_does_not_grow_with_the_users(): void
    {
        $admin = User::factory()->admin()->create();
        $token = $admin->createToken('admin')->plainTextToken;
        User::factory()->create();

        $fewUsers = $this->countQueries(fn () => $this->withToken($token)->getJson('/api/admin/users')->assertOk());

        User::factory()->count(13)->create();
        $manyUsers = $this->countQueries(fn () => $this->withToken($token)->getJson('/api/admin/users')->assertOk());

        $this->assertSame($fewUsers, $manyUsers);
    }

    /**
     * How many queries the callback runs. Forgets the logged-in user first, so every request checks the token again,
     * and moves the clock 1 minute: Sanctum only updates last_used_at when the time changed, so without it
     * two requests in the same second would differ by that one query.
     */
    private function countQueries(callable $callback): int
    {
        $this->app['auth']->forgetGuards();
        $this->travel(1)->minutes();
        DB::flushQueryLog();
        DB::enableQueryLog();

        $callback();

        $count = count(DB::getQueryLog());
        DB::disableQueryLog();

        return $count;
    }
}
