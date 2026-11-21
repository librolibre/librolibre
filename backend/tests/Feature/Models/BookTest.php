<?php

namespace Tests\Feature\Models;

use App\Models\BindingType;
use App\Models\Book;
use App\Models\BookCondition;
use App\Models\BookPhoto;
use App\Models\Category;
use App\Models\Defect;
use App\Models\Language;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class BookTest extends TestCase
{
    use RefreshDatabase;

    /** Columns that must have a value (NOT NULL in the migration). */
    public static function requiredColumns(): array
    {
        return [
            'title' => ['title'],
            'author' => ['author'],
            'owner' => ['user_id'],
            'language' => ['language_id'],
            'binding type' => ['binding_type_id'],
            'condition' => ['condition_id'],
        ];
    }

    /** Columns that can be empty (Open Library does not always have them). */
    public static function optionalColumns(): array
    {
        return [
            'isbn' => ['isbn'],
            'cover' => ['cover_url'],
            'publisher' => ['publisher'],
            'publication year' => ['publication_year'],
            'page count' => ['page_count'],
            'weight' => ['weight'],
            'notes' => ['notes'],
        ];
    }

    /** A new book is available and trade only by default. */
    public function test_factory_creates_a_book_with_default_values(): void
    {
        $book = Book::factory()->create()->fresh();

        $this->assertSame('available', $book->status);
        $this->assertFalse($book->is_donation);
    }

    #[DataProvider('requiredColumns')]
    public function test_required_columns_cannot_be_null(string $column): void
    {
        $this->expectException(QueryException::class);
        $this->expectExceptionMessage('insert into "books"');

        Book::factory()->create([$column => null]);
    }

    #[DataProvider('optionalColumns')]
    public function test_optional_columns_can_be_null(string $column): void
    {
        $book = Book::factory()->create([$column => null])->fresh();

        $this->assertNull($book->$column);
    }

    /** Two neighbours can own the same book, so the ISBN is not unique. */
    public function test_two_books_can_have_the_same_isbn(): void
    {
        Book::factory()->create(['isbn' => '9788420412146']);
        Book::factory()->create(['isbn' => '9788420412146']);

        $this->assertSame(2, Book::where('isbn', '9788420412146')->count());
    }

    /** The owner and the status can only be changed by our own code. */
    public function test_owner_and_status_cannot_be_mass_assigned(): void
    {
        $book = Book::factory()->create();
        $ownerId = $book->user_id;

        $book->fill([
            'user_id' => User::factory()->create()->id,
            'status' => 'hidden',
        ])->save();

        $book->refresh();
        $this->assertSame($ownerId, $book->user_id);
        $this->assertSame('available', $book->status);
    }

    /** A user who still has books cannot be deleted (no cascade on user_id). */
    public function test_user_with_books_cannot_be_deleted(): void
    {
        $book = Book::factory()->create();

        $this->expectException(QueryException::class);

        $book->user->delete();
    }

    // ------------------------------------------------------------------
    // Relationships
    // ------------------------------------------------------------------

    public function test_book_belongs_to_a_user(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($book->user->is($user));
        $this->assertTrue($user->books->contains($book));
    }

    public function test_book_belongs_to_a_language(): void
    {
        $language = Language::create(['code' => 'en', 'name' => 'Inglés']);
        $book = Book::factory()->create(['language_id' => $language->id]);

        $this->assertTrue($book->language->is($language));
    }

    public function test_book_belongs_to_a_binding_type(): void
    {
        $bindingType = BindingType::create(['code' => 'hardcover', 'name' => 'Tapa dura']);
        $book = Book::factory()->create(['binding_type_id' => $bindingType->id]);

        $this->assertTrue($book->bindingType->is($bindingType));
    }

    public function test_book_belongs_to_a_condition(): void
    {
        $condition = BookCondition::create(['code' => 'new', 'name' => 'Nuevo']);
        $book = Book::factory()->create(['condition_id' => $condition->id]);

        $this->assertTrue($book->condition->is($condition));
    }

    /** Attaching from the category side writes the row in book_category that Book::categories() reads. */
    public function test_book_belongs_to_many_categories(): void
    {
        $book = Book::factory()->create();
        $category = Category::create(['code' => 'novel', 'name' => 'Novela']);

        $category->books()->attach($book);

        $this->assertTrue($book->categories->contains($category));
    }

    /** The composite primary key stops the same book–category pair being saved twice. */
    public function test_book_cannot_be_linked_to_the_same_category_twice(): void
    {
        $book = Book::factory()->create();
        $category = Category::create(['code' => 'novel', 'name' => 'Novela']);
        $book->categories()->attach($category);

        $this->expectException(UniqueConstraintViolationException::class);

        $book->categories()->attach($category);
    }

    /** Deleting a book also deletes its category and defect links (cascade on book_id). */
    public function test_deleting_book_removes_its_category_and_defect_links(): void
    {
        $book = Book::factory()->create();
        $category = Category::create(['code' => 'novel', 'name' => 'Novela']);
        $defect = Defect::create(['code' => 'underlining', 'name' => 'Subrayado o resaltado']);

        $book->categories()->attach($category);
        $book->defects()->attach($defect);
        $book->delete();

        $this->assertDatabaseMissing('book_category', ['book_id' => $book->id]);
        $this->assertDatabaseMissing('book_defect', ['book_id' => $book->id]);
    }

    /** Attaching from the defect side writes the row in book_defect that Book::defects() reads. */
    public function test_book_belongs_to_many_defects(): void
    {
        $book = Book::factory()->create();
        $defect = Defect::create(['code' => 'underlining', 'name' => 'Subrayado o resaltado']);

        $defect->books()->attach($book);

        $this->assertTrue($book->defects->contains($defect));
    }

    /** The composite primary key stops the same book–defect pair being saved twice. */
    public function test_book_cannot_be_linked_to_the_same_defect_twice(): void
    {
        $book = Book::factory()->create();
        $defect = Defect::create(['code' => 'underlining', 'name' => 'Subrayado o resaltado']);
        $book->defects()->attach($defect);

        $this->expectException(UniqueConstraintViolationException::class);

        $book->defects()->attach($defect);
    }

    /** Photos come back in the order chosen by the owner. */
    public function test_book_has_many_photos_ordered_by_position(): void
    {
        $book = Book::factory()->create();
        $second = BookPhoto::factory()->create(['book_id' => $book->id, 'position' => 2]);
        $first = BookPhoto::factory()->create(['book_id' => $book->id, 'position' => 1]);

        $this->assertSame([$first->id, $second->id], $book->photos->pluck('id')->all());
    }
}
