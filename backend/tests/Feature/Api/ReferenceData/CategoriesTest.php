<?php

namespace Tests\Feature\Api\ReferenceData;

use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoriesTest extends TestCase
{
    use RefreshDatabase;

    /** GET /categories returns the 17 categories of the seeder. */
    public function test_categories_returns_the_seventeen_seeded_categories(): void
    {
        $this->seed(CategorySeeder::class);
        $expected = [
            'novel', 'poetry', 'essay', 'children', 'young_adult', 'comic', 'science_fiction', 'fantasy', 'history',
            'biography', 'self_help', 'crime', 'romance', 'horror', 'textbook', 'cooking', 'other',
        ];

        $response = $this->getJson('/api/categories');

        $response->assertOk();
        $response->assertJsonCount(17, 'data');
        $this->assertEqualsCanonicalizing($expected, $response->json('data.*.code'));
    }

    /** Each category only has code and name (no id or timestamps), like Category in the contract. */
    public function test_categories_only_show_code_and_name(): void
    {
        $this->seed(CategorySeeder::class);

        $response = $this->getJson('/api/categories');

        $response->assertJsonPath('data.0', fn (array $category) => array_keys($category) === ['code', 'name']);
        $response->assertJsonFragment(['code' => 'science_fiction', 'name' => 'Ciencia ficción']);
    }

    /** Sorted by name, so the list is easy to scan in a dropdown. */
    public function test_categories_are_sorted_by_name(): void
    {
        $this->seed(CategorySeeder::class);

        $response = $this->getJson('/api/categories');

        $this->assertSame(
            [
                'Autoayuda', 'Biografía', 'Ciencia ficción', 'Cocina', 'Cómic', 'Ensayo', 'Fantasía', 'Historia', 'Infantil',
                'Juvenil', 'Libros de texto', 'Novela', 'Novela negra', 'Otros', 'Poesía', 'Romántica', 'Terror',
            ],
            $response->json('data.*.name'),
        );
    }

    /** The list is public: visitors need it too (catalogue filters, for example). */
    public function test_categories_do_not_require_a_token(): void
    {
        $this->getJson('/api/categories')->assertOk();
    }
}
