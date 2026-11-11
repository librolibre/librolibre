<?php

namespace Tests\Feature\Models;

use App\Models\Category;
use Database\Seeders\CategorySeeder;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    /** The seeder creates all the categories currently available. */
    public function test_seeder_creates_all_expected_categories(): void
    {
        $this->seed(CategorySeeder::class);
        $expected = [
            'novel', 'poetry', 'essay', 'children', 'young_adult', 'comic',
            'science_fiction', 'fantasy', 'history', 'biography', 'self_help',
            'crime', 'romance', 'horror', 'textbook', 'cooking', 'other',
            ];
        $this->assertEqualsCanonicalizing($expected, Category::pluck('code')->all());

        // $this->assertGreaterThanOrEqual(17, Category::count());
    }

    /** Running the seeder again (for example on Neon) must not duplicate rows. */
    public function test_category_seeder_can_run_twice_without_duplicates(): void
    {
        $this->seed(CategorySeeder::class);
        $countFirstRun = Category::count();

        $this->seed(CategorySeeder::class);

        $this->assertSame($countFirstRun, Category::count());
    }

    /** `code` is what the code compares against, so it can never repeat. */
    public function test_category_code_must_be_unique(): void
    {
        Category::create(['code' => 'novel', 'name' => 'Novela']);

        $this->expectException(UniqueConstraintViolationException::class);

        Category::create(['code' => 'novel', 'name' => 'RepeatNovela']);
    }

    /** Two categories with the same name would look identical in the dropdown. */
    public function test_category_name_must_be_unique(): void
    {
        Category::create(['code' => 'novel', 'name' => 'Novela']);

        $this->expectException(UniqueConstraintViolationException::class);

        Category::create(['code' => 'novela2', 'name' => 'Novela']);
    }

    // ------------------------------------------------------------------
    // Relationships
    // ------------------------------------------------------------------

    // public function test_category_has_many_books(): void
    // {
    //     $category = Category::create(['code' => 'novel', 'name' => 'Novela']);
    //     $book = Book::factory()->create();
    //     $book->categories()->attach($category);

    //     $this->assertTrue($category->books->contains($book));
    // }
}
