<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Dedoc\Scramble\Attributes\Group;

/**
 * STUB: todos los métodos devuelven datos fijos. No hay lógica ni base de datos.
 * Los parámetros de ruta son escalares (no modelos) a propósito: con route model binding
 * Laravel iría a la base de datos y devolvería 404.
 */
#[Group('Suggestions')]
class SuggestionController extends Controller
{
    /**
     * Sugerir libros cercanos
     *
     * RF-38 (Baja). Mismo autor o categoría que los pedidos o publicados.
     */
    public function index()
    {
        return response()->json([
            'data' => [
                [
                    'id' => 42,
                    'isbn' => '9788420412146',
                    'title' => 'Cien años de soledad',
                    'author' => 'Gabriel García Márquez',
                    'publisher' => 'Alfaguara',
                    'publication_year' => 1,
                    'publication_place' => 'Madrid',
                    'edition_name' => 'Edición conmemorativa',
                    'page_count' => 3,
                    'dimensions' => '21 x 14 cm',
                    'weight' => 1,
                    'cover_image_url' => 'https://example.com/storage/cover_image_url.jpg',
                    'categories' => [
                        [
                            'code' => 'novel',
                            'name' => 'Novela',
                        ],
                    ],
                    'language' => 'es',
                    'binding_type' => 'paperback',
                    'condition' => 'new',
                    'defects' => ['underlining'],
                    'is_donation' => true,
                    'notes' => 'Algún subrayado a lápiz en los primeros capítulos.',
                    'photos' => [
                        [
                            'id' => 1,
                            'url' => 'https://example.com/storage/url.jpg',
                        ],
                    ],
                    'status' => 'available',
                    'distance_km' => 1.2,
                    'owner' => [
                        'id' => 3,
                        'username' => 'Ane',
                        'photo_url' => 'https://example.com/storage/photo_url.jpg',
                        'description' => 'Lectora de novela y ensayo.',
                        'languages' => ['es', 'eu'],
                        'average_rating' => 4.6,
                        'ratings_count' => 5,
                        'exchanges_count' => 7,
                        'member_since' => '2026-11-15T10:00:00Z',
                        'blocked_by_me' => false,
                    ],
                    'is_mine' => false,
                    'created_at' => '2026-11-15T10:00:00Z',
                ],
            ],
        ]);
    }
}
