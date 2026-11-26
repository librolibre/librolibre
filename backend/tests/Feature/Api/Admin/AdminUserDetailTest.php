<?php

namespace Tests\Feature\Api\Admin;

use App\Models\AdminAction;
use App\Models\Book;
use App\Models\Offer;
use App\Models\Rating;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * GET /admin/users/{user} (#34, CU-17): what an admin sees about one account.
 * Expected shape: AdminUserDetail in docs/api/openapi.yaml (AdminUser fields + books, offers, ratings,
 * reports_received and actions).
 *
 * Written from the expected behaviour, not from the current code: some tests fail until #34 is finished.
 *
 * Still to add in Sprint 4 (#127, after offers #65 and reports #77):
 * - offers: the offers the user sent AND the ones received on their books, newest first
 * - reports_received: reports against this user (not the ones they made), with reason, status and who resolved it
 */
class AdminUserDetailTest extends TestCase
{
    use RefreshDatabase;

    /** Admin with a token, for the requests. */
    private function adminToken(?User $admin = null): string
    {
        $admin ??= User::factory()->admin()->create();

        return $admin->createToken('admin')->plainTextToken;
    }

    /** A rating received by $rated, on its own offer so the ratings don't share one. */
    private function rateUser(User $rated, int $score, ?User $rater = null): Rating
    {
        return Rating::factory()->create([
            'offer_id' => Offer::factory(),
            'user_rater_id' => ($rater ?? User::factory()->create())->id,
            'user_rated_id' => $rated->id,
            'score' => $score,
        ]);
    }

    // ------------------------------------------------------------------
    // Access
    // ------------------------------------------------------------------

    /** Without a token, 401. */
    public function test_detail_requires_a_token(): void
    {
        $user = User::factory()->create();

        $this->getJson('/api/admin/users/'.$user->id)->assertUnauthorized();
    }

    /** A regular user can't see another user's detail: 403. */
    public function test_regular_user_gets_403(): void
    {
        $regular = User::factory()->create();
        $user = User::factory()->create();

        $this->withToken($regular->createToken('user')->plainTextToken)
            ->getJson('/api/admin/users/'.$user->id)
            ->assertForbidden();
    }

    /** A user that doesn't exist: 404. */
    public function test_unknown_user_returns_404(): void
    {
        $this->withToken($this->adminToken())
            ->getJson('/api/admin/users/999999')
            ->assertNotFound();
    }

    // ------------------------------------------------------------------
    // Account summary (same fields as the list, AdminUser)
    // ------------------------------------------------------------------

    /** The detail starts with the same account data as the list. */
    public function test_detail_returns_the_account_summary(): void
    {
        $user = User::factory()->create([
            'username' => 'Ane',
            'email' => 'ane@example.com',
            'strikes' => 2,
            'photo_path' => null,
        ]);
        Book::factory()->count(2)->for($user)->create();

        $response = $this->withToken($this->adminToken())->getJson('/api/admin/users/'.$user->id);

        $response->assertOk();
        $response->assertJsonPath('data.id', $user->id);
        $response->assertJsonPath('data.username', 'Ane');
        $response->assertJsonPath('data.email', 'ane@example.com');
        $response->assertJsonPath('data.status', 'active');
        $response->assertJsonPath('data.role', 'user');
        $response->assertJsonPath('data.strikes', 2);
        $response->assertJsonPath('data.books_count', 2);
        $response->assertJsonPath('data.photo_url', null);
        $this->assertNotNull($response->json('data.created_at'));
    }

    /** The detail of a suspended user can still be seen (the admin needs it to reactivate). */
    public function test_suspended_user_detail_can_be_seen(): void
    {
        $user = User::factory()->create(['status' => 'suspended']);

        $response = $this->withToken($this->adminToken())->getJson('/api/admin/users/'.$user->id);

        $response->assertOk();
        $response->assertJsonPath('data.status', 'suspended');
    }

    /** average_rating is the mean of the ratings received, rounded to 1 decimal: 5, 4, 4 → 4.3. */
    public function test_average_rating_is_rounded_to_one_decimal(): void
    {
        $user = User::factory()->create();
        foreach ([5, 4, 4] as $score) {
            $this->rateUser($user, $score);
        }

        $response = $this->withToken($this->adminToken())->getJson('/api/admin/users/'.$user->id);

        $response->assertOk();
        $this->assertEquals(4.3, $response->json('data.average_rating'));
    }

    /** Without ratings, average_rating is null (not 0). */
    public function test_average_rating_is_null_without_ratings(): void
    {
        $user = User::factory()->create();

        $response = $this->withToken($this->adminToken())->getJson('/api/admin/users/'.$user->id);

        $response->assertOk();
        $response->assertJsonPath('data.average_rating', null);
    }

    // ------------------------------------------------------------------
    // Books
    // ------------------------------------------------------------------

    /** books lists all the user's books, in any status (hidden and withdrawn too), and no one else's. */
    public function test_books_lists_only_this_users_books_in_any_status(): void
    {
        $user = User::factory()->create();
        $mine = collect(['available', 'paused', 'hidden', 'withdrawn'])
            ->map(fn (string $status) => Book::factory()->for($user)->status($status)->create());
        Book::factory()->create(); // another user's book

        $response = $this->withToken($this->adminToken())->getJson('/api/admin/users/'.$user->id);

        $response->assertOk();
        $this->assertEqualsCanonicalizing($mine->pluck('id')->all(), $response->json('data.books.*.id'));
    }

    /** Each book has at least what the admin panel shows: id, title, author and status. */
    public function test_each_book_has_title_author_and_status(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->for($user)->status('hidden')->create([
            'title' => 'Obabakoak',
            'author' => 'Bernardo Atxaga',
        ]);

        $response = $this->withToken($this->adminToken())->getJson('/api/admin/users/'.$user->id);

        $response->assertOk();
        $response->assertJsonPath('data.books.0.id', $book->id);
        $response->assertJsonPath('data.books.0.title', 'Obabakoak');
        $response->assertJsonPath('data.books.0.author', 'Bernardo Atxaga');
        $response->assertJsonPath('data.books.0.status', 'hidden');
    }

    /** Books come newest first. */
    public function test_books_are_newest_first(): void
    {
        $user = User::factory()->create();
        $old = Book::factory()->for($user)->create(['created_at' => now()->subDays(2)]);
        $new = Book::factory()->for($user)->create(['created_at' => now()]);

        $response = $this->withToken($this->adminToken())->getJson('/api/admin/users/'.$user->id);

        $response->assertOk();
        $this->assertSame([$new->id, $old->id], $response->json('data.books.*.id'));
    }

    /** A user without books gets an empty list, not a missing key. */
    public function test_books_is_an_empty_list_without_books(): void
    {
        $user = User::factory()->create();

        $response = $this->withToken($this->adminToken())->getJson('/api/admin/users/'.$user->id);

        $response->assertOk();
        $response->assertJsonPath('data.books', []);
    }

    // ------------------------------------------------------------------
    // Ratings
    // ------------------------------------------------------------------

    /** ratings has the ratings the user received, not the ones they gave. */
    public function test_ratings_lists_only_ratings_received(): void
    {
        $user = User::factory()->create();
        $received = $this->rateUser($user, 5);
        $this->rateUser(User::factory()->create(), 2, rater: $user); // given by the user

        $response = $this->withToken($this->adminToken())->getJson('/api/admin/users/'.$user->id);

        $response->assertOk();
        $this->assertSame([$received->id], $response->json('data.ratings.*.id'));
    }

    /** Each rating shows who gave it, the score and the comment. */
    public function test_rating_includes_the_rater_score_and_comment(): void
    {
        $user = User::factory()->create();
        $rater = User::factory()->create(['username' => 'Mikel']);
        $rating = $this->rateUser($user, 4, rater: $rater);
        $rating->update(['comment' => 'Todo bien']);

        $response = $this->withToken($this->adminToken())->getJson('/api/admin/users/'.$user->id);

        $response->assertOk();
        $response->assertJsonPath('data.ratings.0.rater.id', $rater->id);
        $response->assertJsonPath('data.ratings.0.rater.username', 'Mikel');
        $response->assertJsonPath('data.ratings.0.score', 4);
        $response->assertJsonPath('data.ratings.0.comment', 'Todo bien');
    }

    // ------------------------------------------------------------------
    // Admin actions on the account (key "actions" in the contract)
    // ------------------------------------------------------------------

    /** After suspending and reactivating through the API, actions shows both, newest first, with admin and reason. */
    public function test_actions_show_suspension_and_reactivation_newest_first(): void
    {
        $admin = User::factory()->admin()->create(['username' => 'Admin']);
        $token = $this->adminToken($admin);
        $user = User::factory()->create();

        $this->withToken($token)
            ->postJson('/api/admin/users/'.$user->id.'/suspend', ['reason' => 'Insultos en el chat'])
            ->assertOk();
        $this->travel(1)->minutes();
        $this->withToken($token)
            ->postJson('/api/admin/users/'.$user->id.'/reactivate', ['reason' => 'Revisado: no había motivo'])
            ->assertOk();

        $response = $this->withToken($token)->getJson('/api/admin/users/'.$user->id);

        $response->assertOk();
        $this->assertSame(['reactivate_user', 'suspend_user'], $response->json('data.actions.*.action'));
        $response->assertJsonPath('data.actions.1.admin.id', $admin->id);
        $response->assertJsonPath('data.actions.1.admin.username', 'Admin');
        $response->assertJsonPath('data.actions.1.reason', 'Insultos en el chat');
        $this->assertNotNull($response->json('data.actions.1.created_at'));
    }

    /**
     * actions are the ones done ON this account, not the ones this user did as an admin,
     * and not the ones done on other users.
     */
    public function test_actions_only_include_actions_on_this_account(): void
    {
        $viewedAdmin = User::factory()->admin()->create();
        $other = User::factory()->create();

        // Done BY the viewed admin on someone else: must not appear
        AdminAction::factory()->create([
            'admin_id' => $viewedAdmin->id,
            'target_type' => 'user',
            'target_id' => $other->id,
        ]);
        // Done on a book whose id happens to be the viewed user's id: must not appear
        AdminAction::factory()->create([
            'action' => 'hide_book',
            'target_type' => 'book',
            'target_id' => $viewedAdmin->id,
        ]);

        $response = $this->withToken($this->adminToken())->getJson('/api/admin/users/'.$viewedAdmin->id);

        $response->assertOk();
        $response->assertJsonPath('data.actions', []);
    }

    /** An automatic suspension (3 strikes, RN-18) has no admin: admin is null. */
    public function test_automatic_suspension_has_no_admin(): void
    {
        $user = User::factory()->create(['status' => 'suspended', 'strikes' => 3]);
        AdminAction::factory()->automatic()->create([
            'action' => 'auto_suspend',
            'target_type' => 'user',
            'target_id' => $user->id,
            'reason' => null,
        ]);

        $response = $this->withToken($this->adminToken())->getJson('/api/admin/users/'.$user->id);

        $response->assertOk();
        $response->assertJsonPath('data.actions.0.action', 'auto_suspend');
        $response->assertJsonPath('data.actions.0.admin', null);
    }
}
