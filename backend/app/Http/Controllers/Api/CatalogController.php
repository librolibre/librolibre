<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Request;

/**
 * STUB: todos los métodos devuelven datos fijos. No hay lógica ni base de datos.
 * Los parámetros de ruta son escalares (no modelos) a propósito: con route model binding
 * Laravel iría a la base de datos y devolvería 404.
 */
#[Group('Catalog')]
class CatalogController extends Controller
{
    /**
     * Buscar en el catálogo
     *
     * RF-13 a RF-19. No incluye los libros propios ni de los otros usuarios bloqueados (en ambos sentidos)
     * ni tampoco los pausados, ocultos, retirados o entregados. Los reservados se incluyen con
     * `status: reserved`. Un visitante (sin token) recibe los resultados sin `distance_km` (RF-02).
     * Debe responder en menos de 3 segundos.
     */
    public function index(Request $request)
    {
        $request->validate([
            /** Título, autor o ISBN. */
            'q' => ['sometimes', 'string'],
            /** Código de la categoría. */
            'category' => ['sometimes', 'string'],
            /** Código ISO 639-1. */
            'language' => ['sometimes', 'string'],
            /** Estado mínimo; devuelve ese estado o mejores. */
            'min_condition' => ['sometimes', 'in:new,like_new,very_good,good,acceptable'],
            /** Excluye los libros con los defectos especificados(e.g., «sin subrayados»). */
            'exclude_defects' => ['sometimes', 'array'],
            'exclude_defects.*' => ['in:underlining,margin_notes,doodles,name_or_dedication,damaged_cover,damaged_spine,folded_pages,loose_pages,water_damage,yellowed_pages,odor,child_use,missing_material,library_stamp'],
            'binding_type' => ['sometimes', 'in:paperback,hardcover,pocket,other'],
            /** `true`: solo libros que se pueden pedir en donación. */
            'is_donation' => ['sometimes', 'boolean'],
            'year_min' => ['sometimes', 'integer'],
            'year_max' => ['sometimes', 'integer'],
            /** En km. Requiere ubicación en el perfil. */
            'max_distance' => ['sometimes', 'numeric'],
            'sort' => ['sometimes', 'in:distance,newest,title'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ]);

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
            'links' => [
                'first' => 'http://localhost:8000/api/catalog?page=1',
                'last' => 'http://localhost:8000/api/catalog?page=5',
                'prev' => 'http://localhost:8000/api/catalog?page=1',
                'next' => 'http://localhost:8000/api/catalog?page=3',
            ],
            'meta' => [
                'current_page' => 1,
                'last_page' => 5,
                'per_page' => 20,
                'total' => 93,
            ],
        ]);
    }

    /**
     * Obtener los marcadores del mapa
     *
     * RF-17, HU-12. Mismos filtros que `/catalog`, sin paginar, con un máximo de 200 marcadores.
     * Las coordenadas son aproximadas y se muestran con la API de Google Maps.
     * Disponible para visitantes, igual que el catálogo (CU-04).
     */
    public function map(Request $request)
    {
        $request->validate([
            'q' => ['sometimes', 'string'],
            /** Código de la categoría. */
            'category' => ['sometimes', 'string'],
            'is_donation' => ['sometimes', 'boolean'],
            'max_distance' => ['sometimes', 'numeric'],
        ]);

        return response()->json([
            'data' => [
                [
                    'book_id' => 1,
                    'title' => 'Cien años de soledad',
                    'cover_image_url' => 'https://example.com/storage/cover_image_url.jpg',
                    'location' => [
                        'latitude' => 42.85,
                        'longitude' => -2.67,
                    ],
                ],
            ],
        ]);
    }
}
