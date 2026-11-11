<?php

namespace Tests\Feature\Models;

// use App\Models\Book;
use App\Models\Language;
use Database\Seeders\LanguageSeeder;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LanguageTest extends TestCase
{
    use RefreshDatabase;

    /** The seeder creates all the languages being offered. */
    public function test_seeder_creates_all_expected_language_codes(): void
    {
        // $this->seed(LanguageSeeder::class);
        // $this->assertSame(
        //     // 'es' => 'Español', 
        //     // 'eu' => 'Euskera', 
        //     // 'en' => 'Inglés',
        //     // 'fr' => 'Francés',
        //     // 'ca' => 'Catalán',
        //     // 'gl' => 'Gallego',
        //     // 'de' => 'Alemán',
        //     // 'pt' => 'Portugués',
        //     // 'it' => 'Italiano'
        //     ['ca', 'de', 'en', 'es', 'eu', 'fr', 'gl', 'it', 'pt'],
        //     Language::pluck('code')->sort()->values()->all(),
        // );

        $this->seed(LanguageSeeder::class);
        $expected = ['es', 'eu', 'en', 'fr', 'ca', 'gl', 'de', 'pt', 'it'];
        $this->assertEqualsCanonicalizing($expected, Language::pluck('code')->all());
    }

    /** Running the seeder again (for example on Neon) must not duplicate rows. */
    public function test_language_seeder_can_run_twice_without_duplicates(): void
    {
        $this->seed(LanguageSeeder::class);
        $countFirstRun = Language::count();

        $this->seed(LanguageSeeder::class);

        $this->assertSame($countFirstRun, Language::count());
    }

    /** `code` is what the code compares against, so it can never repeat. */
    public function test_language_code_must_be_unique(): void
    {
        Language::create(['code' => 'es', 'name' => 'Español']);

        $this->expectException(UniqueConstraintViolationException::class);

        Language::create(['code' => 'es', 'name' => 'Spanish']);
    }

    // ------------------------------------------------------------------
    // Relationships
    // ------------------------------------------------------------------

    // public function test_language_has_many_books(): void
    // {
    //     $language = Language::create(['code' => 'es', 'name' => 'Español']);
    //     $book = Book::factory()->create(['language_id' => $language->id]);

    //     $this->assertTrue($language->books->contains($book));
    // }
}
