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
class BookController extends Controller
{
    /**
     * Ver la ficha de un libro
     */
    #[Response(status: 404, description: 'El recurso no existe o no es visible para el usuario.', type: 'array{message: string}')]
    public function show(int $book)
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
     * Publicar un libro
     *
     * RF-06 a RF-11. Se crea automaticamente en estado `available`.
     */
    public function store(Request $request)
    {
        $request->validate([
            /** Vacío si se añade a mano (RF-08). */
            'isbn' => ['nullable', 'string'],
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:255'],
            'publisher' => ['nullable', 'string', 'max:255'],
            'publication_year' => ['nullable', 'integer'],
            'publication_place' => ['nullable', 'string', 'max:255'],
            'edition_name' => ['nullable', 'string', 'max:255'],
            'page_count' => ['nullable', 'integer', 'min:1'],
            'dimensions' => ['nullable', 'string', 'max:50'],
            /** En gramos. */
            'weight' => ['nullable', 'integer'],
            'cover_image_url' => ['nullable', 'string', 'url'],
            /** Códigos de las categorías (los de `GET /categories`). */
            'categories' => ['required', 'array', 'min:1'],
            'categories.*' => ['string', 'distinct'],
            'language' => ['required', 'string', 'min:2', 'max:2'],
            'binding_type' => ['required', 'in:paperback,hardcover,pocket,other'],
            'condition' => ['required', 'in:new,like_new,very_good,good,acceptable'],
            'defects' => ['sometimes', 'array'],
            'defects.*' => ['distinct', 'in:underlining,margin_notes,doodles,name_or_dedication,damaged_cover,damaged_spine,folded_pages,loose_pages,water_damage,yellowed_pages,odor,child_use,missing_material,library_stamp'],
            /** `false` = «Solo trueque»; `true` = «Trueque y donación» (RN-04). */
            'is_donation' => ['required', 'boolean'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        /** @status 201 */
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
     * Editar la ficha de un libro
     *
     * Solo el propietario del libro puede editar su ficha (RF-12).
     */
    #[Response(status: 403, description: 'El usuario no tiene permiso para esta acción.', type: 'array{message: string}')]
    #[Response(status: 404, description: 'El recurso no existe o no es visible para el usuario.', type: 'array{message: string}')]
    public function update(Request $request, int $book)
    {
        $request->validate([
            /** Vacío si se añade a mano (RF-08). */
            'isbn' => ['nullable', 'string'],
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:255'],
            'publisher' => ['nullable', 'string', 'max:255'],
            'publication_year' => ['nullable', 'integer'],
            'publication_place' => ['nullable', 'string', 'max:255'],
            'edition_name' => ['nullable', 'string', 'max:255'],
            'page_count' => ['nullable', 'integer', 'min:1'],
            'dimensions' => ['nullable', 'string', 'max:50'],
            /** En gramos. */
            'weight' => ['nullable', 'integer'],
            'cover_image_url' => ['nullable', 'string', 'url'],
            /** Códigos de las categorías (los de `GET /categories`). */
            'categories' => ['required', 'array', 'min:1'],
            'categories.*' => ['string', 'distinct'],
            'language' => ['required', 'string', 'min:2', 'max:2'],
            'binding_type' => ['required', 'in:paperback,hardcover,pocket,other'],
            'condition' => ['required', 'in:new,like_new,very_good,good,acceptable'],
            'defects' => ['sometimes', 'array'],
            'defects.*' => ['distinct', 'in:underlining,margin_notes,doodles,name_or_dedication,damaged_cover,damaged_spine,folded_pages,loose_pages,water_damage,yellowed_pages,odor,child_use,missing_material,library_stamp'],
            /** `false` = «Solo trueque»; `true` = «Trueque y donación» (RN-04). */
            'is_donation' => ['required', 'boolean'],
            'notes' => ['nullable', 'string', 'max:500'],
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
     * Retirar un libro
     *
     * Pasa a `withdrawn` (estado final). No se permite retirar el libro de la lista si está reservado en
     * un intercambio aceptado.
     */
    #[Response(status: 403, description: 'El usuario no tiene permiso para esta acción.', type: 'array{message: string}')]
    #[Response(status: 409, description: 'La operación incumple una regla de negocio.', type: 'array{message: string, code: string}')]
    public function destroy(int $book)
    {
        return response()->noContent();
    }

    /**
     * Pausar un libro (es decir parar temporalmente la disponibilidad de un libro)
     */
    #[Response(status: 403, description: 'El usuario no tiene permiso para esta acción.', type: 'array{message: string}')]
    #[Response(status: 409, description: 'La operación incumple una regla de negocio.', type: 'array{message: string, code: string}')]
    public function pause(int $book)
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
     * Reactivar un libro pausado
     */
    #[Response(status: 403, description: 'El usuario no tiene permiso para esta acción.', type: 'array{message: string}')]
    #[Response(status: 409, description: 'La operación incumple una regla de negocio.', type: 'array{message: string, code: string}')]
    public function resume(int $book)
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
}
