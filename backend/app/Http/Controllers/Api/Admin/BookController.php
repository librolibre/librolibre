<?php

namespace App\Http\Controllers\Api\Admin;

use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\Response;
use Illuminate\Http\Request;

/**
 * STUB: todos los métodos devuelven datos fijos. No hay lógica ni base de datos.
 * Los parámetros de ruta son escalares (no modelos) a propósito: con route model binding
 * Laravel iría a la base de datos y devolvería 404.
 */
#[Group('Admin')]
class BookController extends \App\Http\Controllers\Controller
{
    /**
     * Mostrar lista de los libros publicados
     *
     * RF-35, CU-19.
     */
    #[Response(status: 403, description: 'El usuario no tiene permiso para esta acción.', type: 'array{message: string}')]
    public function index(Request $request)
    {
        $request->validate([
            /** Título, autor o propietario. */
            'q' => ['sometimes', 'string'],
            'status' => ['sometimes', 'in:available,locked,reserved,delivered,paused,hidden,withdrawn'],
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
                            'id' => 1,
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
                'first' => 'http://localhost:8000/api/admin/books?page=1',
                'last' => 'http://localhost:8000/api/admin/books?page=5',
                'prev' => 'http://localhost:8000/api/admin/books?page=1',
                'next' => 'http://localhost:8000/api/admin/books?page=3',
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
     * Ocultar un libro
     *
     * RN-18. Cancela sus solicitudes en curso y avisa al propietario con el motivo. Si el libro
     * está reservado, hay que enviar `confirm_cancellation: true` (CU-19, 4b); si no, responde
     * `409` con `code: BOOK_RESERVED`.
     */
    #[Response(status: 403, description: 'El usuario no tiene permiso para esta acción.', type: 'array{message: string}')]
    #[Response(status: 409, description: 'La operación incumple una regla de negocio.', type: 'array{message: string, code: string}')]
    public function hide(Request $request, int $book)
    {
        $request->validate([
            'reason' => ['required', 'string', 'max:500'],
            'confirm_cancellation' => ['sometimes', 'boolean'],
        ]);

        return response()->json([
            'data' => [
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
                        'id' => 1,
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
        ]);
    }

    /**
     * Restaurar un libro oculto
     */
    #[Response(status: 403, description: 'El usuario no tiene permiso para esta acción.', type: 'array{message: string}')]
    #[Response(status: 409, description: 'La operación incumple una regla de negocio.', type: 'array{message: string, code: string}')]
    public function restore(int $book)
    {
        return response()->json([
            'data' => [
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
                        'id' => 1,
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
        ]);
    }
}
