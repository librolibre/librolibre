<?php

namespace Tests\Feature\Models;

use App\Models\BookCondition;
use Database\Seeders\BookConditionSeeder;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookConditionTest extends TestCase
{
    use RefreshDatabase;

    /** The seeder creates every book condition (same codes as the API contract). */
    public function test_seeder_creates_all_expected_book_conditions(): void
    {
        $this->seed(BookConditionSeeder::class);
        $expected = [
            'new', 'like_new', 'very_good', 'good', 'acceptable',
            ];
        $this->assertEqualsCanonicalizing($expected, BookCondition::pluck('code')->all());
    }

    /** Running the seeder again (for example on Neon) must not duplicate rows. */
    public function test_book_condition_seeder_can_run_twice_without_duplicates(): void
    {
        $this->seed(BookConditionSeeder::class);
        $countFirstRun = BookCondition::count();

        $this->seed(BookConditionSeeder::class);

        $this->assertSame($countFirstRun, BookCondition::count());
    }

    /** `code` is what the code compares against, so it can never repeat. */
    public function test_book_condition_code_must_be_unique(): void
    {
        BookCondition::create(['code' => 'new', 'name' => 'Nuevo']);

        $this->expectException(UniqueConstraintViolationException::class);

        BookCondition::create(['code' => 'new', 'name' => 'Recién comprado']);
    }

    /** Two conditions with the same name would look identical in the dropdown. */
    public function test_book_condition_name_must_be_unique(): void
    {
        BookCondition::create(['code' => 'new', 'name' => 'Nuevo']);

        $this->expectException(UniqueConstraintViolationException::class);

        BookCondition::create(['code' => 'new2', 'name' => 'Nuevo']);
    }

    // ------------------------------------------------------------------
    // Relationships
    // ------------------------------------------------------------------

    // public function test_book_condition_has_many_books(): void
    // {
    //     $condition = BookCondition::create(['code' => 'new', 'name' => 'Nuevo']);
    //     $book = Book::factory()->create(['condition_id' => $condition->id]);

    //     $this->assertTrue($condition->books->contains($book));
    // }
}
