<?php

namespace Tests\Feature\Api\Profile;

use App\Models\Book;
use App\Models\Language;
use App\Models\User;
use Database\Seeders\ProfileSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Laravel\Socialite\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Tests\TestCase;

/**
 * DELETE /me (#29): the account is anonymised, not removed, because other users'
 * exchanges, messages and ratings still point to it.
 *
 * Still to add in Sprint 4 (when offers and notifications exist):
 * - pending, counteroffer and accepted offers become cancelled, and the other side is notified (RN-16)
 * - ratings already published are still shown, signed as «Usuario eliminado»
 * Sprint 3: a deleted user's books do not appear in the catalogue search.
 */
class DeleteAccountTest extends TestCase
{
    use RefreshDatabase;

    // ------------------------------------------------------------------
    // The response and the tokens
    // ------------------------------------------------------------------

    /** Deleting the account answers 204 with no body. */
    public function test_delete_account_returns_204(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('user')->plainTextToken;

        $response = $this->withToken($token)->deleteJson('/api/me');

        $response->assertNoContent();
    }

    /** Every token of the user stops working, not only the one used for the request. */
    public function test_all_tokens_are_revoked(): void
    {
        $user = User::factory()->create();

        $officeToken = $user->createToken('office')->plainTextToken;
        $homeToken = $user->createToken('home')->plainTextToken;

        $response = $this->withToken($officeToken)->deleteJson('/api/me');
        $response->assertNoContent();
        $this->app['auth']->forgetGuards();

        $this->withToken($homeToken)->getJson('/api/me')->assertUnauthorized();
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_delete_account_requires_a_token(): void
    {
        $this->deleteJson('/api/me')->assertUnauthorized();
    }

    // ------------------------------------------------------------------
    // What happens to the user row
    // ------------------------------------------------------------------

    /** The row stays, with status deleted. */
    public function test_status_becomes_deleted(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('user')->plainTextToken;

        $this->withToken($token)->deleteJson('/api/me')->assertNoContent();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'status' => 'deleted',
        ]);

        $this->assertSame('deleted', $user->fresh()->status);
    }

    /** Photo path, description, postal code, coordinates and languages are cleared. */
    public function test_personal_data_is_removed(): void
    {
        $user = User::factory()->create([
            'description' => 'This is a description.',
            'postal_code' => '01005',
            'latitude' => 42.85,
            'longitude' => -2.67,
            'photo_path' => 'photo.jpg',
        ]);
        $user->languages()->attach(Language::firstOrCreate(['code' => 'es'], ['name' => 'Español']));
        $token = $user->createToken('user')->plainTextToken;

        $this->withToken($token)->deleteJson('/api/me')->assertNoContent();

        $fresh = $user->fresh();

        $removedFields = [
            'description', 'postal_code', 'latitude',
            'longitude', 'photo_path',
        ];

        foreach ($removedFields as $field) {
            $this->assertNull($fresh->{$field});
        }

        $this->assertCount(0, $fresh->languages);
    }

    /** The photo file is deleted from the photos disk too. */
    public function test_photo_file_is_deleted(): void
    {
        $photosDisk = Storage::fake(config('filesystems.photos_disk'));

        $user = User::factory()->create(['photo_path' => 'profile-photos/foto.jpg']);
        $photosDisk->put($user->photo_path, 'old photo');

        $token = $user->createToken('user')->plainTextToken;

        $this->withToken($token)->deleteJson('/api/me')->assertNoContent();

        $photosDisk->assertMissing('profile-photos/foto.jpg');
    }

    /** Email, google_id and username are replaced by values that identify nobody and stay unique. */
    public function test_identifiers_are_anonymised(): void
    {
        $user = User::factory()->create([
            'google_id' => '123456789',
            'email' => 'ane@example.com',
            'username' => 'Ane la de los libros',
        ]);
        $token = $user->createToken('user')->plainTextToken;

        $this->withToken($token)->deleteJson('/api/me')->assertNoContent();

        $fresh = $user->fresh();

        $this->assertSame('deleted-'.$user->id, $fresh->google_id);
        $this->assertSame('deleted-'.$user->id.'@librolibre.invalid', $fresh->email);
        $this->assertSame('usuario-eliminado-'.$user->id, $fresh->username);

        // email is unique, so no row with the old one means it is really gone
        $this->assertDatabaseMissing('users', ['email' => 'ane@example.com']);
    }

    /** Logging in again with the same Google account creates a brand-new account. */
    public function test_logging_in_again_with_google_creates_a_new_account(): void
    {
        // Arrange: a user with a known Google id deletes their account
        $this->seed(ProfileSeeder::class);

        $user = User::factory()->create(['google_id' => '123456789', 'username' => 'Ane la de los libros']);
        $token = $user->createToken('user')->plainTextToken;

        $this->withToken($token)->deleteJson('/api/me')->assertNoContent();

        // Act: the same person logs in again with the same Google account
        Socialite::fake('google', (new SocialiteUser)->map([
            'id' => '123456789',
            'email' => 'ane@example.com',
            'name' => 'Ane Banane',
            'avatar' => 'https://photo_path',
        ])->setRaw(['given_name' => 'Ane']));

        $response = $this->get('/api/auth/google/callback');

        // Assert: the login works, and it is a new row, not the deleted one
        $response->assertRedirectContains(config('app.frontend_url').'/auth/callback?code=');

        $this->assertDatabaseCount('users', 2);

        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
            'google_id' => '123456789',
        ]);

        $this->assertDatabaseHas('users', [
            'google_id' => '123456789',
            'status' => 'active',
        ]);
    }

    // ------------------------------------------------------------------
    // What happens to the user's books and blocks
    // ------------------------------------------------------------------

    /** The user's books become withdrawn, so they leave the catalogue (RN-16). */
    public function test_books_are_withdrawn(): void
    {
        // Arrange: books in different statuses, and a book of someone else
        $user = User::factory()->create();
        $available = Book::factory()->for($user)->status('available')->create();
        $paused = Book::factory()->for($user)->status('paused')->create();
        $delivered = Book::factory()->for($user)->status('delivered')->create();
        $otherBook = Book::factory()->status('available')->create();

        $token = $user->createToken('user')->plainTextToken;

        // Act
        $this->withToken($token)->deleteJson('/api/me')->assertNoContent();

        // Assert: the books still on offer are withdrawn
        $this->assertSame('withdrawn', $available->fresh()->status);
        $this->assertSame('withdrawn', $paused->fresh()->status);

        // A delivered book is already part of a finished exchange: it stays as history
        $this->assertSame('delivered', $delivered->fresh()->status);

        // Only this user's books change
        $this->assertSame('available', $otherBook->fresh()->status);
    }

    /** The user's blocks are removed. */
    public function test_blocks_are_removed(): void
    {
        // Arrange: the user blocks Ane, Mikel blocks the user, and Ane blocks Mikel (not related)
        $user = User::factory()->create();
        $ane = User::factory()->create(['username' => 'Ane']);
        $mikel = User::factory()->create(['username' => 'Mikel']);

        $user->blockedUsers()->attach($ane);
        $mikel->blockedUsers()->attach($user);
        $ane->blockedUsers()->attach($mikel);

        $token = $user->createToken('user')->plainTextToken;

        // Act
        $this->withToken($token)->deleteJson('/api/me')->assertNoContent();

        // Assert: both directions are gone
        $this->assertDatabaseMissing('blocks', [
            'user_blocker_id' => $user->id,
            'user_blocked_id' => $ane->id,
        ]);
        $this->assertDatabaseMissing('blocks', [
            'user_blocker_id' => $mikel->id,
            'user_blocked_id' => $user->id,
        ]);

        // Blocks between other users stay
        $this->assertDatabaseHas('blocks', [
            'user_blocker_id' => $ane->id,
            'user_blocked_id' => $mikel->id,
        ]);
        $this->assertDatabaseCount('blocks', 1);
    }

    // ------------------------------------------------------------------
    // After the deletion
    // ------------------------------------------------------------------

    /** The deleted account's public profile returns 404. */
    public function test_deleted_account_profile_returns_404(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('user')->plainTextToken;

        $this->withToken($token)->deleteJson('/api/me')->assertNoContent();

        // Forget the deleted user, so the profile is requested as a visitor
        $this->app['auth']->forgetGuards();

        $response = $this->getJson('/api/users/'.$user->id);

        $response->assertNotFound();
        $response->assertJsonStructure(['message']);
    }
}
