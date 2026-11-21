<?php

// namespace Feature\Api\ReferenceData;
namespace Tests\Feature\Api\ReferenceData;

use Database\Seeders\LanguageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LanguagesTest extends TestCase
{
    use RefreshDatabase;

    /** GET /languages returns the 9 languages of the seeder. */
    public function test_languages_returns_the_nine_seeded_languages(): void
    {
        $this->seed(LanguageSeeder::class);
        $expected = ['es', 'eu', 'en', 'fr', 'ca', 'gl', 'de', 'pt', 'it'];
        // $this->assertEqualsCanonicalizing($expected, Language::pluck('code')->all());

        $response = $this->getJson('/api/languages');

        $response->assertOk();
        $response->assertJsonCount(9, 'data');
        $this->assertEqualsCanonicalizing($expected, $response->json('data.*.code'));
    }

    /** Each language only has code and name (no id or timestamps), like Language in the contract. */
    public function test_languages_only_show_code_and_name(): void
    {
        $this->seed(LanguageSeeder::class);

        $response = $this->getJson('/api/languages');

        $response->assertJsonPath('data.0', fn (array $language) => array_keys($language) === ['code', 'name']);
        $response->assertJsonFragment(['code' => 'eu', 'name' => 'Euskera']);
    }

    /** Sorted by name, so the list is easy to scan in a dropdown. */
    public function test_languages_are_sorted_by_name(): void
    {
        $this->seed(LanguageSeeder::class);

        $response = $this->getJson('/api/languages');

        $this->assertSame(
            ['Alemán', 'Catalán', 'Español', 'Euskera', 'Francés', 'Gallego', 'Inglés', 'Italiano', 'Portugués'],
            $response->json('data.*.name'),
        );
    }

    /** The list is public: visitors need it too (book filters, for example). */
    public function test_languages_do_not_require_a_token(): void
    {
        $this->getJson('/api/languages')->assertOk();
    }
}
