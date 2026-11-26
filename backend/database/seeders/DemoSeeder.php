<?php

namespace Database\Seeders;

use App\Models\BindingType;
use App\Models\Book;
use App\Models\BookCondition;
use App\Models\Category;
use App\Models\Language;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Http\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Demo data (#14): ~20 users and ~40 real books, so the front end and the demo have something to show.
 * Run by hand, never automatically: `php artisan db:seed --class=DemoSeeder`.
 *
 * It goes into the shared database, so every demo row is marked and never touches real users:
 * - email "<slug>@demo.librolibre.test" (.test is a reserved domain) and google_id "demo-<slug>"
 *   (no real Google account can have it, so nobody logs in as a demo user by accident);
 * - safe to run again: it updates the demo rows instead of duplicating them;
 * - `php artisan demo:clear` removes all of it; `php artisan demo:token <email>` gives a token for a demo user.
 *
 * The data lives in database/seeders/demo/users.php and books.php.
 */
class DemoSeeder extends Seeder
{
    public const EMAIL_DOMAIN = 'demo.librolibre.test';

    public const GOOGLE_ID_PREFIX = 'demo-';

    public const PHOTOS_FOLDER = 'profile-photos/demo';

    public function run(): void
    {
        // Reference data the demo rows point to (these seeders are safe to run again)
        $this->call([
            ProfileSeeder::class,
            LanguageSeeder::class,
            CategorySeeder::class,
            BindingTypeSeeder::class,
            BookConditionSeeder::class,
        ]);

        $users = require database_path('seeders/demo/users.php');
        $books = require database_path('seeders/demo/books.php');

        // All or nothing: a half-loaded demo would be harder to clean up
        DB::transaction(function () use ($users, $books) {
            $demoUsers = $this->seedUsers($users);
            $this->seedSuspensions($users, $demoUsers);
            $this->seedBlocks($demoUsers);
            $this->seedBooks($books, $demoUsers);
        });
    }

    /**
     * @param  array<int, array<string, mixed>>  $users
     * @return array<string, User> demo users by slug
     */
    private function seedUsers(array $users): array
    {
        $profileIds = Profile::pluck('id', 'code');
        $languageIds = Language::pluck('id', 'code');
        $demoUsers = [];

        foreach ($users as $data) {
            // firstOrNew by email: the second run finds the same row and updates it
            $user = User::firstOrNew(['email' => $data['slug'].'@'.self::EMAIL_DOMAIN]);

            $user->fill([
                'username' => $data['username'],
                'google_id' => self::GOOGLE_ID_PREFIX.$data['slug'],
                'description' => $data['description'],
                'postal_code' => $data['postal_code'],
                'latitude' => $data['latitude'],
                'longitude' => $data['longitude'],
                'privacy_accepted_at' => $user->privacy_accepted_at ?? now(),
            ]);
            // Not fillable on purpose (a request must never set them), so they are assigned one by one
            $user->profile_id = $profileIds[$data['role'] ?? 'user'];
            $user->status = $data['status'] ?? 'active';
            $user->strikes = $data['strikes'] ?? 0;
            $user->photo_path = $this->storePhoto($data['slug']);
            $user->save();

            $user->languages()->sync($languageIds->only($data['languages'])->values());

            $demoUsers[$data['slug']] = $user;
        }

        return $demoUsers;
    }

    // Copia el avatar al disco de fotos; nombre fijo para no duplicarlo - Laurence
    private function storePhoto(string $slug): ?string
    {
        $source = database_path("seeders/demo/photos/{$slug}.png");

        if (! is_file($source)) {
            return null;
        }

        return Storage::disk(config('filesystems.photos_disk'))
            ->putFileAs(self::PHOTOS_FOLDER, new File($source), "{$slug}.png");
    }

    /**
     * The suspended demo users get the admin_actions row an admin would have left (#36).
     *
     * @param  array<int, array<string, mixed>>  $users
     * @param  array<string, User>  $demoUsers
     */
    private function seedSuspensions(array $users, array $demoUsers): void
    {
        $admin = $demoUsers['laura'];

        foreach ($users as $data) {
            if (($data['status'] ?? 'active') !== 'suspended') {
                continue;
            }

            // firstOrCreate: only one suspension row per user, however many times this runs
            $admin->adminActions()->firstOrCreate(
                ['action' => 'suspend_user', 'target_type' => 'user', 'target_id' => $demoUsers[$data['slug']]->id],
                ['reason' => $data['suspension_reason']],
            );
        }
    }

    /**
     * A few blocks, so the front end can try them (#73): the blocked user gets 404 on the blocker's profile.
     *
     * @param  array<string, User>  $demoUsers
     */
    private function seedBlocks(array $demoUsers): void
    {
        $blocks = [
            'ane' => 'sergio',
            'mikel' => 'lucia',
            'nerea' => 'jon',
        ];

        foreach ($blocks as $blocker => $blocked) {
            // syncWithoutDetaching: adds the block if missing, never duplicates it
            $demoUsers[$blocker]->blockedUsers()->syncWithoutDetaching([$demoUsers[$blocked]->id]);
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $books
     * @param  array<string, User>  $demoUsers
     */
    private function seedBooks(array $books, array $demoUsers): void
    {
        $languageIds = Language::pluck('id', 'code');
        $categoryIds = Category::pluck('id', 'code');
        $bindingTypeIds = BindingType::pluck('id', 'code');
        $conditionIds = BookCondition::pluck('id', 'code');

        foreach ($books as $data) {
            $owner = $demoUsers[$data['owner']];

            // The same owner and title is the same book (the ISBN can be null)
            $book = Book::where('user_id', $owner->id)->where('title', $data['title'])->first() ?? new Book;

            $book->fill([
                'title' => $data['title'],
                'author' => $data['author'],
                'isbn' => $data['isbn'],
                'language_id' => $languageIds[$data['language']],
                'publisher' => $data['publisher'],
                'publication_year' => $data['publication_year'],
                'page_count' => $data['page_count'],
                'cover_url' => $data['cover_url'],
                'binding_type_id' => $bindingTypeIds[$data['binding_type']],
                'condition_id' => $conditionIds[$data['condition']],
                'is_donation' => $data['is_donation'],
            ]);
            // Not fillable on purpose, like in the API
            $book->user_id = $owner->id;
            $book->status = $data['status'];
            $book->save();

            $book->categories()->sync($categoryIds->only($data['categories'])->values());
        }
    }
}
