<?php

namespace Tests\Feature\Models;

use App\Models\Book;
use App\Models\BookPhoto;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookPhotoTest extends TestCase
{
    use RefreshDatabase;

    /** A photo is saved with its path and position 0 by default. */
    public function test_factory_creates_a_photo(): void
    {
        $photo = BookPhoto::factory()->create()->fresh();

        $this->assertStringStartsWith('books/', $photo->path);
        $this->assertSame(0, $photo->position);
    }

    /** A photo always belongs to a book. */
    public function test_book_is_required(): void
    {
        $this->expectException(QueryException::class);

        BookPhoto::factory()->create(['book_id' => null]);
    }

    public function test_path_is_required(): void
    {
        $this->expectException(QueryException::class);

        BookPhoto::factory()->create(['path' => null]);
    }

    /** Deleting a book also deletes its photos (cascade on book_id). */
    public function test_deleting_book_removes_its_photos(): void
    {
        $photo = BookPhoto::factory()->create();

        $photo->book->delete();

        $this->assertDatabaseMissing('book_photos', ['id' => $photo->id]);
    }

    // ------------------------------------------------------------------
    // Relationships
    // ------------------------------------------------------------------

    public function test_photo_belongs_to_a_book(): void
    {
        $book = Book::factory()->create();
        $photo = BookPhoto::factory()->create(['book_id' => $book->id]);

        $this->assertTrue($photo->book->is($book));
    }
}
