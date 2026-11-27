<?php

namespace Tests\Feature\Demo;

use App\Models\AdminAction;
use App\Models\Book;
use App\Models\User;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Demo data (#14): DemoSeeder, `php artisan demo:token` and `php artisan demo:clear`.
 * The demo data goes to the shared database, so it must be recognisable (email domain and google_id prefix),
 * never touch real users, be safe to run twice, and be removable with one command.
 */
class DemoDataTest extends TestCase
{
    use RefreshDatabase;

    /** The seeder copies avatars to the photos disk: a fake disk, so the tests never write real files. */
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake(config('filesystems.photos_disk'));
    }

    /** @return array<int, array<string, mixed>> */
    private function demoUsers(): array
    {
        return require database_path('seeders/demo/users.php');
    }

    /** @return array<int, array<string, mixed>> */
    private function demoBooks(): array
    {
        return require database_path('seeders/demo/books.php');
    }

    private function demoUserQuery()
    {
        return User::where('email', 'like', '%@'.DemoSeeder::EMAIL_DOMAIN);
    }

    // ------------------------------------------------------------------
    // DemoSeeder
    // ------------------------------------------------------------------

    /** It creates every user and book in the data files, with the expected admins and suspended users. */
    public function test_seeder_creates_the_demo_users_and_books(): void
    {
        $this->seed(DemoSeeder::class);

        $this->assertSame(count($this->demoUsers()), $this->demoUserQuery()->count());
        $this->assertSame(count($this->demoBooks()), Book::whereIn('user_id', $this->demoUserQuery()->select('id'))->count());

        $this->assertSame(2, $this->demoUserQuery()->get()->filter->isAdmin()->count());
        $this->assertSame(2, $this->demoUserQuery()->where('status', 'suspended')->count());
    }

    /** Every demo user carries both marks, so they can always be found (and Google can never log in as them). */
    public function test_every_demo_user_is_marked(): void
    {
        $this->seed(DemoSeeder::class);

        foreach ($this->demoUserQuery()->get() as $user) {
            $this->assertStringEndsWith('@'.DemoSeeder::EMAIL_DOMAIN, $user->email);
            $this->assertStringStartsWith(DemoSeeder::GOOGLE_ID_PREFIX, $user->google_id);
        }
    }

    /** Running it twice changes nothing: no duplicated users, books, languages, blocks or admin actions. */
    public function test_running_the_seeder_twice_does_not_duplicate_anything(): void
    {
        $tables = ['users', 'books', 'book_category', 'language_user', 'blocks', 'admin_actions'];

        $this->seed(DemoSeeder::class);
        $first = collect($tables)->mapWithKeys(fn ($table) => [$table => DB::table($table)->count()]);

        $this->seed(DemoSeeder::class);
        $second = collect($tables)->mapWithKeys(fn ($table) => [$table => DB::table($table)->count()]);

        $this->assertSame($first->all(), $second->all());
    }

    /** Each suspended demo user has the admin action that suspended them. */
    public function test_suspended_demo_users_have_their_admin_action(): void
    {
        $this->seed(DemoSeeder::class);

        foreach ($this->demoUserQuery()->where('status', 'suspended')->get() as $user) {
            $this->assertDatabaseHas('admin_actions', [
                'action' => 'suspend_user',
                'target_type' => 'user',
                'target_id' => $user->id,
            ]);
        }
    }

    /** A real user (not demo) is not changed by the seeder. */
    public function test_seeder_does_not_touch_real_users(): void
    {
        $fields = ['username', 'email', 'google_id', 'profile_id', 'status', 'strikes'];
        $real = User::factory()->create(['username' => 'Real', 'email' => 'real@example.com']);
        // fresh(): read from the database, so status and strikes have their default values
        $before = $real->fresh()->only($fields);

        $this->seed(DemoSeeder::class);

        $this->assertSame($before, $real->fresh()->only($fields));
    }

    /** Users with a PNG in demo/photos get it as their photo; the ones without (Jon, Pablo, Lucía) keep null. */
    public function test_seeder_stores_the_demo_photos(): void
    {
        $this->seed(DemoSeeder::class);
        $disk = Storage::disk(config('filesystems.photos_disk'));

        $ane = $this->demoUserQuery()->where('username', 'Ane')->first();
        $this->assertSame(DemoSeeder::PHOTOS_FOLDER.'/ane.png', $ane->photo_path);
        $disk->assertExists($ane->photo_path);

        $this->assertNull($this->demoUserQuery()->where('username', 'Jon')->value('photo_path'));

        // One photo per PNG, at a fixed path: running it again does not add files
        $this->seed(DemoSeeder::class);
        $pngs = glob(database_path('seeders/demo/photos/*.png'));
        $this->assertCount(count($pngs), $disk->files(DemoSeeder::PHOTOS_FOLDER));
    }

    /**
     * Deleting a demo account removes its photo file, but not the PNG in the repo:
     * seeding again creates a new user with the photo back, and the old (anonymised) row points to nothing.
     */
    public function test_reseeding_after_deleting_a_demo_user_restores_the_photo(): void
    {
        $this->seed(DemoSeeder::class);
        $disk = Storage::disk(config('filesystems.photos_disk'));
        $email = 'ane@'.DemoSeeder::EMAIL_DOMAIN;
        $path = DemoSeeder::PHOTOS_FOLDER.'/ane.png';
        $old = User::where('email', $email)->first();

        $this->withToken($old->createToken('test')->plainTextToken)
            ->deleteJson('/api/me')
            ->assertNoContent();

        // The account is anonymised and its photo file is gone
        $this->assertNull($old->fresh()->photo_path);
        $disk->assertMissing($path);

        $this->seed(DemoSeeder::class);

        // A new Ane, with the photo copied again from the repo
        $new = User::where('email', $email)->first();
        $this->assertNotSame($old->id, $new->id);
        $this->assertSame($path, $new->photo_path);
        $disk->assertExists($path);

        // The old row does not share the new file
        $this->assertNull($old->fresh()->photo_path);
    }

    // ------------------------------------------------------------------
    // demo:token
    // ------------------------------------------------------------------

    /** demo:token prints a token that works as that demo user. */
    public function test_demo_token_gives_a_working_token_for_a_demo_user(): void
    {
        $this->seed(DemoSeeder::class);
        $email = 'ane@'.DemoSeeder::EMAIL_DOMAIN;

        $exitCode = Artisan::call('demo:token', ['email' => $email]);
        // The token is the last word of the output
        $token = trim(collect(preg_split('/\s+/', trim(Artisan::output())))->last());

        $this->assertSame(0, $exitCode);
        $this->withToken($token)->getJson('/api/me')
            ->assertOk()
            ->assertJsonPath('data.email', $email);
    }

    /** demo:token refuses a real user: its token would also be valid against the real API. */
    public function test_demo_token_refuses_a_real_user(): void
    {
        $real = User::factory()->create(['email' => 'real@example.com']);

        $this->artisan('demo:token', ['email' => 'real@example.com'])->assertFailed();

        $this->assertDatabaseMissing('personal_access_tokens', ['tokenable_id' => $real->id]);
    }

    // ------------------------------------------------------------------
    // demo:clear
    // ------------------------------------------------------------------

    /** demo:clear removes every demo row and leaves the real data alone. */
    public function test_demo_clear_removes_the_demo_data_and_keeps_real_data(): void
    {
        // Arrange: real data next to the demo data
        $real = User::factory()->create(['email' => 'real@example.com']);
        $realBook = Book::factory()->for($real)->create();
        $this->seed(DemoSeeder::class);
        $demoIds = $this->demoUserQuery()->pluck('id');
        Artisan::call('demo:token', ['email' => 'ane@'.DemoSeeder::EMAIL_DOMAIN]);

        // Act
        $this->artisan('demo:clear', ['--force' => true])->assertSuccessful();

        // Assert: no demo rows anywhere
        $this->assertSame(0, $this->demoUserQuery()->count());
        $this->assertSame(0, Book::whereIn('user_id', $demoIds)->count());
        $this->assertSame(0, AdminAction::whereIn('admin_id', $demoIds)->count());
        $this->assertSame(0, DB::table('personal_access_tokens')->whereIn('tokenable_id', $demoIds)->count());

        // The real user and their book are still there
        $this->assertModelExists($real);
        $this->assertModelExists($realBook);
    }

    /** demo:clear also deletes the demo users' photo files from the photos disk. */
    public function test_demo_clear_deletes_the_demo_photos(): void
    {
        $this->seed(DemoSeeder::class);
        $paths = $this->demoUserQuery()->whereNotNull('photo_path')->pluck('photo_path');
        $this->assertNotEmpty($paths);

        $this->artisan('demo:clear', ['--force' => true])->assertSuccessful();

        foreach ($paths as $path) {
            Storage::disk(config('filesystems.photos_disk'))->assertMissing($path);
        }
    }

    /** Without --force it asks first; answering no deletes nothing. */
    public function test_demo_clear_asks_before_deleting(): void
    {
        $this->seed(DemoSeeder::class);
        $before = $this->demoUserQuery()->count();

        $this->artisan('demo:clear')
            ->expectsConfirmation('¿Borrar todos los datos de prueba (usuarios @'.DemoSeeder::EMAIL_DOMAIN.' y sus libros)?', 'no')
            ->assertSuccessful();

        $this->assertSame($before, $this->demoUserQuery()->count());
    }
}
