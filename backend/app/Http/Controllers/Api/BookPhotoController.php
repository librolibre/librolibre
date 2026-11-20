<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\Response;
use Illuminate\Http\Request;

/**
 * STUB: todos los métodos devuelven datos fijos. No hay lógica ni base de datos.
 * Los parámetros de ruta son escalares (no modelos) a propósito: con route model binding
 * Laravel iría a la base de datos y devolvería 404.
 */
#[Group('My books')]
class BookPhotoController extends Controller
{
    /**
     * Añadir fotos a la ficha de un libro
     *
     * Se pueden subir hasta 5 fotos de un libro.
     */
    #[Response(status: 403, description: 'El usuario no tiene permiso para esta acción.', type: 'array{message: string}')]
    public function store(Request $request, int $book)
    {
        $request->validate([
            'photos' => ['required', 'array', 'max:5'],
            'photos.*' => ['file', 'image'],
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
        ]);
    }

    /**
     * Eliminar una foto de la ficha del un libro
     */
    #[Response(status: 403, description: 'El usuario no tiene permiso para esta acción.', type: 'array{message: string}')]
    #[Response(status: 404, description: 'El recurso no existe o no es visible para el usuario.', type: 'array{message: string}')]
    public function destroy(int $book, int $photo)
    {
        return response()->noContent();
    }
}
