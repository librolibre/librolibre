<?php

namespace Tests\Feature\Models;

use App\Models\Book;
use App\Models\Defect;
use Database\Seeders\DefectSeeder;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DefectTest extends TestCase
{
    use RefreshDatabase;

    /** The seeder creates every defect a user can mark on a book (same codes as the API contract). */
    public function test_seeder_creates_all_expected_defects(): void
    {
        $this->seed(DefectSeeder::class);
        $expected = [
            'underlining', 'margin_notes', 'doodles', 'name_or_dedication', 'damaged_cover',
            'damaged_spine', 'folded_pages', 'loose_pages', 'water_damage', 'yellowed_pages',
            'odor', 'child_use', 'missing_material', 'library_stamp',
            ];
        $this->assertEqualsCanonicalizing($expected, Defect::pluck('code')->all());
    }

    /** Running the seeder again (for example on Neon) must not duplicate rows. */
    public function test_defect_seeder_can_run_twice_without_duplicates(): void
    {
        $this->seed(DefectSeeder::class);
        $countFirstRun = Defect::count();

        $this->seed(DefectSeeder::class);

        $this->assertSame($countFirstRun, Defect::count());
    }

    /** `code` is what the code compares against, so it can never repeat. */
    public function test_defect_code_must_be_unique(): void
    {
        Defect::create(['code' => 'underlining', 'name' => 'Subrayado o resaltado']);

        $this->expectException(UniqueConstraintViolationException::class);

        Defect::create(['code' => 'underlining', 'name' => 'Subsaltado o rerayado']);
    }

    /** Two defects with the same name would look identical in the dropdown. */
    public function test_defect_name_must_be_unique(): void
    {
        Defect::create(['code' => 'underlining', 'name' => 'Subrayado o resaltado']);

        $this->expectException(UniqueConstraintViolationException::class);

        Defect::create(['code' => 'underlining2', 'name' => 'Subrayado o resaltado']);
    }

    // ------------------------------------------------------------------
    // Relationships
    // ------------------------------------------------------------------

    /** Attaching from the book side writes the row in book_defect that Defect::books() reads. */
    public function test_defect_belongs_to_many_books(): void
    {
        $defect = Defect::create(['code' => 'underlining', 'name' => 'Subrayado o resaltado']);
        $book = Book::factory()->create();

        $book->defects()->attach($defect);

        $this->assertTrue($defect->books->contains($book));
    }

    /** Deleting a defect also deletes its links to books (cascade on defect_id). */
    public function test_deleting_defect_removes_its_book_links(): void
    {
        $defect = Defect::create(['code' => 'underlining', 'name' => 'Subrayado o resaltado']);
        $book = Book::factory()->create();

        $defect->books()->attach($book);
        $defect->delete();

        $this->assertDatabaseMissing('book_defect', [
            'book_id' => $book->id,
            'defect_id' => $defect->id,
        ]);
    }
}
