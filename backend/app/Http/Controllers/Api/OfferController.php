<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\Response;
use Illuminate\Http\Request;

/**
 * STUB: todos los métodos devuelven datos fijos. No hay lógica ni base de datos.
 * Los parámetros de ruta son int (no modelos) a propósito: con route model binding
 * Laravel iría a la base de datos y devolvería 404.
 */
#[Group('Offers')]
class OfferController extends Controller
{
    /**
     * Mostrar lista de mis solicitudes
     *
     * RF-26. Pestañas de enviadas, recibidas e historial.
     */
    public function index(Request $request)
    {
        $request->validate([
            /** Pestaña: enviadas, recibidas o historial. */
            'inbox' => ['required', 'in:sent,received,history'],
            /** Solo las solicitudes sobre este libro propio (para elegir destinatario de una donación, RF-23). */
            'book_id' => ['sometimes', 'integer'],
            /** Número de página (20 elementos por página). */
            'page' => ['sometimes', 'integer', 'min:1'],
        ]);

        return response()->json([
            'data' => [
                [
                    'id' => 15,
                    'type' => 'trade',
                    'status' => 'pending',
                    'requested_book' => [
                        'id' => 42,
                        'isbn' => '9788420412146',
                        'title' => 'Cien años de soledad',
                        'author' => 'Gabriel García Márquez',
                        'publisher' => 'Alfaguara',
                        'publication_year' => 1967,
                        'publication_place' => 'Madrid',
                        'edition_name' => 'Edición conmemorativa',
                        'page_count' => 471,
                        'dimensions' => '21 x 14 cm',
                        'weight' => 450,
                        'cover_image_url' => 'https://example.com/storage/covers/42.jpg',
                        'categories' => [
                            [
                                'id' => 1,
                                'name' => 'Novela',
                            ],
                        ],
                        'language' => 'es',
                        'binding_type' => 'paperback',
                        'condition' => 'good',
                        'defects' => ['underlining'],
                        'is_donation' => true,
                        'notes' => 'Algún subrayado a lápiz en los primeros capítulos.',
                        'photos' => [
                            [
                                'id' => 1,
                                'url' => 'https://example.com/storage/books/42/1.jpg',
                            ],
                        ],
                        'status' => 'available',
                        'distance_km' => 1.2,
                        'owner' => [
                            'id' => 3,
                            'username' => 'Ane',
                            'photo_url' => 'https://example.com/storage/photos/ane.jpg',
                            'description' => 'Lectora de novela y ensayo.',
                            'languages' => ['es', 'eu'],
                            'average_rating' => 4.6,
                            'ratings_count' => 5,
                            'exchanges_count' => 7,
                            'member_since' => '2026-01-15T10:00:00Z',
                            'blocked_by_me' => false,
                        ],
                        'is_mine' => false,
                        'created_at' => '2026-11-02T13:30:00Z',
                    ],
                    'offered_book' => [
                        'id' => 57,
                        'isbn' => '9788497592451',
                        'title' => 'El amor en los tiempos del cólera',
                        'author' => 'Gabriel García Márquez',
                        'publisher' => 'Alfaguara',
                        'publication_year' => 1967,
                        'publication_place' => 'Madrid',
                        'edition_name' => 'Edición conmemorativa',
                        'page_count' => 471,
                        'dimensions' => '21 x 14 cm',
                        'weight' => 450,
                        'cover_image_url' => 'https://example.com/storage/covers/57.jpg',
                        'categories' => [
                            [
                                'id' => 1,
                                'name' => 'Novela',
                            ],
                        ],
                        'language' => 'es',
                        'binding_type' => 'paperback',
                        'condition' => 'good',
                        'defects' => ['underlining'],
                        'is_donation' => true,
                        'notes' => 'Algún subrayado a lápiz en los primeros capítulos.',
                        'photos' => [
                            [
                                'id' => 1,
                                'url' => 'https://example.com/storage/books/57/1.jpg',
                            ],
                        ],
                        'status' => 'available',
                        'distance_km' => 1.2,
                        'owner' => [
                            'id' => 3,
                            'username' => 'Ane',
                            'photo_url' => 'https://example.com/storage/photos/ane.jpg',
                            'description' => 'Lectora de novela y ensayo.',
                            'languages' => ['es', 'eu'],
                            'average_rating' => 4.6,
                            'ratings_count' => 5,
                            'exchanges_count' => 7,
                            'member_since' => '2026-01-15T10:00:00Z',
                            'blocked_by_me' => false,
                        ],
                        'is_mine' => false,
                        'created_at' => '2026-11-02T13:30:00Z',
                    ],
                    'requester' => [
                        'id' => 3,
                        'username' => 'Ane',
                        'photo_url' => 'https://example.com/storage/photos/ane.jpg',
                    ],
                    'owner' => [
                        'id' => 8,
                        'username' => 'Iker',
                        'photo_url' => 'https://example.com/storage/photos/iker.jpg',
                    ],
                    'message' => 'Hola, me interesa tu libro. ¿Te parece bien el cambio?',
                    'counteroffer_made' => false,
                    'delivery_confirmed_by_me' => false,
                    'delivery_confirmed_by_other' => false,
                    'chat_read_only' => false,
                    'unread_messages' => 2,
                    'can_rate' => false,
                    'expires_at' => '2026-11-22T13:30:00Z',
                    'created_at' => '2026-11-15T10:00:00Z',
                    'updated_at' => '2026-11-15T10:00:00Z',
                ],
            ],
            'links' => [
                'first' => 'http://localhost:8000/api/offers?page=1',
                'last' => 'http://localhost:8000/api/offers?page=5',
                'prev' => 'http://localhost:8000/api/offers?page=1',
                'next' => 'http://localhost:8000/api/offers?page=3',
            ],
            'meta' => [
                'current_page' => 2,
                'last_page' => 5,
                'per_page' => 20,
                'total' => 93,
            ],
        ]);
    }

    /**
     * Proponer un trueque o pedir una donación
     *
     * RF-20, RF-22. Crea una solicitud en status de `pending`, abre su chat y notifica al propietario.
     * Reglas: RN-02 (no se piden libros propios), RN-03 (un libro ofrecido y uno pedido),
     * RN-04 (donación solo si `is_donation`), RN-05 y RN-07 (el ofrecido queda bloqueado).
     */
    #[Response(status: 409, description: 'La operación incumple una regla de negocio.', type: 'array{message: string, code: string}')]
    public function store(Request $request)
    {
        $request->validate([
            /** Trueque o donación. */
            'type' => ['required', 'in:trade,donation'],
            /** Libro que se pide. */
            'requested_book_id' => ['required', 'integer'],
            /** Obligatorio si `type` es `trade`; vacío si es `donation`. */
            'offered_book_id' => ['nullable', 'integer', 'required_if:type,trade'],
            /** Mensaje opcional para el propietario. */
            'message' => ['nullable', 'string', 'max:500'],
        ]);

        /** @status 201 */
        return response()->json([
            'data' => [
                'id' => 15,
                'type' => 'trade',
                'status' => 'pending',
                'requested_book' => [
                    'id' => 42,
                    'isbn' => '9788420412146',
                    'title' => 'Cien años de soledad',
                    'author' => 'Gabriel García Márquez',
                    'publisher' => 'Alfaguara',
                    'publication_year' => 1967,
                    'publication_place' => 'Madrid',
                    'edition_name' => 'Edición conmemorativa',
                    'page_count' => 471,
                    'dimensions' => '21 x 14 cm',
                    'weight' => 450,
                    'cover_image_url' => 'https://example.com/storage/covers/42.jpg',
                    'categories' => [
                        [
                            'id' => 1,
                            'name' => 'Novela',
                        ],
                    ],
                    'language' => 'es',
                    'binding_type' => 'paperback',
                    'condition' => 'good',
                    'defects' => ['underlining'],
                    'is_donation' => true,
                    'notes' => 'Algún subrayado a lápiz en los primeros capítulos.',
                    'photos' => [
                        [
                            'id' => 1,
                            'url' => 'https://example.com/storage/books/42/1.jpg',
                        ],
                    ],
                    'status' => 'available',
                    'distance_km' => 1.2,
                    'owner' => [
                        'id' => 3,
                        'username' => 'Ane',
                        'photo_url' => 'https://example.com/storage/photos/ane.jpg',
                        'description' => 'Lectora de novela y ensayo.',
                        'languages' => ['es', 'eu'],
                        'average_rating' => 4.6,
                        'ratings_count' => 5,
                        'exchanges_count' => 7,
                        'member_since' => '2026-01-15T10:00:00Z',
                        'blocked_by_me' => false,
                    ],
                    'is_mine' => false,
                    'created_at' => '2026-11-02T13:30:00Z',
                ],
                'offered_book' => [
                    'id' => 57,
                    'isbn' => '9788497592451',
                    'title' => 'El amor en los tiempos del cólera',
                    'author' => 'Gabriel García Márquez',
                    'publisher' => 'Alfaguara',
                    'publication_year' => 1967,
                    'publication_place' => 'Madrid',
                    'edition_name' => 'Edición conmemorativa',
                    'page_count' => 471,
                    'dimensions' => '21 x 14 cm',
                    'weight' => 450,
                    'cover_image_url' => 'https://example.com/storage/covers/57.jpg',
                    'categories' => [
                        [
                            'id' => 1,
                            'name' => 'Novela',
                        ],
                    ],
                    'language' => 'es',
                    'binding_type' => 'paperback',
                    'condition' => 'good',
                    'defects' => ['underlining'],
                    'is_donation' => true,
                    'notes' => 'Algún subrayado a lápiz en los primeros capítulos.',
                    'photos' => [
                        [
                            'id' => 1,
                            'url' => 'https://example.com/storage/books/57/1.jpg',
                        ],
                    ],
                    'status' => 'available',
                    'distance_km' => 1.2,
                    'owner' => [
                        'id' => 3,
                        'username' => 'Ane',
                        'photo_url' => 'https://example.com/storage/photos/ane.jpg',
                        'description' => 'Lectora de novela y ensayo.',
                        'languages' => ['es', 'eu'],
                        'average_rating' => 4.6,
                        'ratings_count' => 5,
                        'exchanges_count' => 7,
                        'member_since' => '2026-01-15T10:00:00Z',
                        'blocked_by_me' => false,
                    ],
                    'is_mine' => false,
                    'created_at' => '2026-11-02T13:30:00Z',
                ],
                'requester' => [
                    'id' => 3,
                    'username' => 'Ane',
                    'photo_url' => 'https://example.com/storage/photos/ane.jpg',
                ],
                'owner' => [
                    'id' => 8,
                    'username' => 'Iker',
                    'photo_url' => 'https://example.com/storage/photos/iker.jpg',
                ],
                'message' => 'Hola, me interesa tu libro. ¿Te parece bien el cambio?',
                'counteroffer_made' => false,
                'delivery_confirmed_by_me' => false,
                'delivery_confirmed_by_other' => false,
                'chat_read_only' => false,
                'unread_messages' => 2,
                'can_rate' => false,
                'expires_at' => '2026-11-22T13:30:00Z',
                'created_at' => '2026-11-15T10:00:00Z',
                'updated_at' => '2026-11-15T10:00:00Z',
            ],
        ]);
    }

    /**
     * Ver una solicitud
     *
     * Solo para las dos partes del intercambio.
     */
    #[Response(status: 403, description: 'El usuario no es parte de la solicitud.', type: 'array{message: string}')]
    #[Response(status: 404, description: 'La solicitud no existe o no es visible para el usuario.', type: 'array{message: string}')]
    public function show(int $offer)
    {
        return response()->json([
            'data' => [
                'id' => 15,
                'type' => 'trade',
                'status' => 'pending',
                'requested_book' => [
                    'id' => 42,
                    'isbn' => '9788420412146',
                    'title' => 'Cien años de soledad',
                    'author' => 'Gabriel García Márquez',
                    'publisher' => 'Alfaguara',
                    'publication_year' => 1967,
                    'publication_place' => 'Madrid',
                    'edition_name' => 'Edición conmemorativa',
                    'page_count' => 471,
                    'dimensions' => '21 x 14 cm',
                    'weight' => 450,
                    'cover_image_url' => 'https://example.com/storage/covers/42.jpg',
                    'categories' => [
                        [
                            'id' => 1,
                            'name' => 'Novela',
                        ],
                    ],
                    'language' => 'es',
                    'binding_type' => 'paperback',
                    'condition' => 'good',
                    'defects' => ['underlining'],
                    'is_donation' => true,
                    'notes' => 'Algún subrayado a lápiz en los primeros capítulos.',
                    'photos' => [
                        [
                            'id' => 1,
                            'url' => 'https://example.com/storage/books/42/1.jpg',
                        ],
                    ],
                    'status' => 'available',
                    'distance_km' => 1.2,
                    'owner' => [
                        'id' => 3,
                        'username' => 'Ane',
                        'photo_url' => 'https://example.com/storage/photos/ane.jpg',
                        'description' => 'Lectora de novela y ensayo.',
                        'languages' => ['es', 'eu'],
                        'average_rating' => 4.6,
                        'ratings_count' => 5,
                        'exchanges_count' => 7,
                        'member_since' => '2026-01-15T10:00:00Z',
                        'blocked_by_me' => false,
                    ],
                    'is_mine' => false,
                    'created_at' => '2026-11-02T13:30:00Z',
                ],
                'offered_book' => [
                    'id' => 57,
                    'isbn' => '9788497592451',
                    'title' => 'El amor en los tiempos del cólera',
                    'author' => 'Gabriel García Márquez',
                    'publisher' => 'Alfaguara',
                    'publication_year' => 1967,
                    'publication_place' => 'Madrid',
                    'edition_name' => 'Edición conmemorativa',
                    'page_count' => 471,
                    'dimensions' => '21 x 14 cm',
                    'weight' => 450,
                    'cover_image_url' => 'https://example.com/storage/covers/57.jpg',
                    'categories' => [
                        [
                            'id' => 1,
                            'name' => 'Novela',
                        ],
                    ],
                    'language' => 'es',
                    'binding_type' => 'paperback',
                    'condition' => 'good',
                    'defects' => ['underlining'],
                    'is_donation' => true,
                    'notes' => 'Algún subrayado a lápiz en los primeros capítulos.',
                    'photos' => [
                        [
                            'id' => 1,
                            'url' => 'https://example.com/storage/books/57/1.jpg',
                        ],
                    ],
                    'status' => 'available',
                    'distance_km' => 1.2,
                    'owner' => [
                        'id' => 3,
                        'username' => 'Ane',
                        'photo_url' => 'https://example.com/storage/photos/ane.jpg',
                        'description' => 'Lectora de novela y ensayo.',
                        'languages' => ['es', 'eu'],
                        'average_rating' => 4.6,
                        'ratings_count' => 5,
                        'exchanges_count' => 7,
                        'member_since' => '2026-01-15T10:00:00Z',
                        'blocked_by_me' => false,
                    ],
                    'is_mine' => false,
                    'created_at' => '2026-11-02T13:30:00Z',
                ],
                'requester' => [
                    'id' => 3,
                    'username' => 'Ane',
                    'photo_url' => 'https://example.com/storage/photos/ane.jpg',
                ],
                'owner' => [
                    'id' => 8,
                    'username' => 'Iker',
                    'photo_url' => 'https://example.com/storage/photos/iker.jpg',
                ],
                'message' => 'Hola, me interesa tu libro. ¿Te parece bien el cambio?',
                'counteroffer_made' => false,
                'delivery_confirmed_by_me' => false,
                'delivery_confirmed_by_other' => false,
                'chat_read_only' => false,
                'unread_messages' => 2,
                'can_rate' => false,
                'expires_at' => '2026-11-22T13:30:00Z',
                'created_at' => '2026-11-15T10:00:00Z',
                'updated_at' => '2026-11-15T10:00:00Z',
            ],
        ]);
    }

    /**
     * Aceptar una solicitud o una contraoferta
     *
     * El propietario acepta una solicitud que está en `pending`, o el solicitante acepta una `counteroffer`.
     * Reserva los libros (RN-08), rechaza las demás solicitudes sobre el libro (RN-06) y cancela el trueque
     * en el que se ofrecía (RN-07).
     */
    #[Response(status: 403, description: 'El usuario no puede aceptar esta solicitud.', type: 'array{message: string}')]
    #[Response(status: 409, description: 'La operación incumple una regla de negocio.', type: 'array{message: string, code: string}')]
    public function accept(int $offer)
    {
        return response()->json([
            'data' => [
                'id' => 15,
                'type' => 'trade',
                'status' => 'pending',
                'requested_book' => [
                    'id' => 42,
                    'isbn' => '9788420412146',
                    'title' => 'Cien años de soledad',
                    'author' => 'Gabriel García Márquez',
                    'publisher' => 'Alfaguara',
                    'publication_year' => 1967,
                    'publication_place' => 'Madrid',
                    'edition_name' => 'Edición conmemorativa',
                    'page_count' => 471,
                    'dimensions' => '21 x 14 cm',
                    'weight' => 450,
                    'cover_image_url' => 'https://example.com/storage/covers/42.jpg',
                    'categories' => [
                        [
                            'id' => 1,
                            'name' => 'Novela',
                        ],
                    ],
                    'language' => 'es',
                    'binding_type' => 'paperback',
                    'condition' => 'good',
                    'defects' => ['underlining'],
                    'is_donation' => true,
                    'notes' => 'Algún subrayado a lápiz en los primeros capítulos.',
                    'photos' => [
                        [
                            'id' => 1,
                            'url' => 'https://example.com/storage/books/42/1.jpg',
                        ],
                    ],
                    'status' => 'available',
                    'distance_km' => 1.2,
                    'owner' => [
                        'id' => 3,
                        'username' => 'Ane',
                        'photo_url' => 'https://example.com/storage/photos/ane.jpg',
                        'description' => 'Lectora de novela y ensayo.',
                        'languages' => ['es', 'eu'],
                        'average_rating' => 4.6,
                        'ratings_count' => 5,
                        'exchanges_count' => 7,
                        'member_since' => '2026-01-15T10:00:00Z',
                        'blocked_by_me' => false,
                    ],
                    'is_mine' => false,
                    'created_at' => '2026-11-02T13:30:00Z',
                ],
                'offered_book' => [
                    'id' => 57,
                    'isbn' => '9788497592451',
                    'title' => 'El amor en los tiempos del cólera',
                    'author' => 'Gabriel García Márquez',
                    'publisher' => 'Alfaguara',
                    'publication_year' => 1967,
                    'publication_place' => 'Madrid',
                    'edition_name' => 'Edición conmemorativa',
                    'page_count' => 471,
                    'dimensions' => '21 x 14 cm',
                    'weight' => 450,
                    'cover_image_url' => 'https://example.com/storage/covers/57.jpg',
                    'categories' => [
                        [
                            'id' => 1,
                            'name' => 'Novela',
                        ],
                    ],
                    'language' => 'es',
                    'binding_type' => 'paperback',
                    'condition' => 'good',
                    'defects' => ['underlining'],
                    'is_donation' => true,
                    'notes' => 'Algún subrayado a lápiz en los primeros capítulos.',
                    'photos' => [
                        [
                            'id' => 1,
                            'url' => 'https://example.com/storage/books/57/1.jpg',
                        ],
                    ],
                    'status' => 'available',
                    'distance_km' => 1.2,
                    'owner' => [
                        'id' => 3,
                        'username' => 'Ane',
                        'photo_url' => 'https://example.com/storage/photos/ane.jpg',
                        'description' => 'Lectora de novela y ensayo.',
                        'languages' => ['es', 'eu'],
                        'average_rating' => 4.6,
                        'ratings_count' => 5,
                        'exchanges_count' => 7,
                        'member_since' => '2026-01-15T10:00:00Z',
                        'blocked_by_me' => false,
                    ],
                    'is_mine' => false,
                    'created_at' => '2026-11-02T13:30:00Z',
                ],
                'requester' => [
                    'id' => 3,
                    'username' => 'Ane',
                    'photo_url' => 'https://example.com/storage/photos/ane.jpg',
                ],
                'owner' => [
                    'id' => 8,
                    'username' => 'Iker',
                    'photo_url' => 'https://example.com/storage/photos/iker.jpg',
                ],
                'message' => 'Hola, me interesa tu libro. ¿Te parece bien el cambio?',
                'counteroffer_made' => false,
                'delivery_confirmed_by_me' => false,
                'delivery_confirmed_by_other' => false,
                'chat_read_only' => false,
                'unread_messages' => 2,
                'can_rate' => false,
                'expires_at' => '2026-11-22T13:30:00Z',
                'created_at' => '2026-11-15T10:00:00Z',
                'updated_at' => '2026-11-15T10:00:00Z',
            ],
        ]);
    }

    /**
     * Rechazar una solicitud o una contraoferta
     */
    #[Response(status: 403, description: 'El usuario no puede rechazar esta solicitud.', type: 'array{message: string}')]
    #[Response(status: 409, description: 'La operación incumple una regla de negocio.', type: 'array{message: string, code: string}')]
    public function reject(int $offer)
    {
        return response()->json([
            'data' => [
                'id' => 15,
                'type' => 'trade',
                'status' => 'pending',
                'requested_book' => [
                    'id' => 42,
                    'isbn' => '9788420412146',
                    'title' => 'Cien años de soledad',
                    'author' => 'Gabriel García Márquez',
                    'publisher' => 'Alfaguara',
                    'publication_year' => 1967,
                    'publication_place' => 'Madrid',
                    'edition_name' => 'Edición conmemorativa',
                    'page_count' => 471,
                    'dimensions' => '21 x 14 cm',
                    'weight' => 450,
                    'cover_image_url' => 'https://example.com/storage/covers/42.jpg',
                    'categories' => [
                        [
                            'id' => 1,
                            'name' => 'Novela',
                        ],
                    ],
                    'language' => 'es',
                    'binding_type' => 'paperback',
                    'condition' => 'good',
                    'defects' => ['underlining'],
                    'is_donation' => true,
                    'notes' => 'Algún subrayado a lápiz en los primeros capítulos.',
                    'photos' => [
                        [
                            'id' => 1,
                            'url' => 'https://example.com/storage/books/42/1.jpg',
                        ],
                    ],
                    'status' => 'available',
                    'distance_km' => 1.2,
                    'owner' => [
                        'id' => 3,
                        'username' => 'Ane',
                        'photo_url' => 'https://example.com/storage/photos/ane.jpg',
                        'description' => 'Lectora de novela y ensayo.',
                        'languages' => ['es', 'eu'],
                        'average_rating' => 4.6,
                        'ratings_count' => 5,
                        'exchanges_count' => 7,
                        'member_since' => '2026-01-15T10:00:00Z',
                        'blocked_by_me' => false,
                    ],
                    'is_mine' => false,
                    'created_at' => '2026-11-02T13:30:00Z',
                ],
                'offered_book' => [
                    'id' => 57,
                    'isbn' => '9788497592451',
                    'title' => 'El amor en los tiempos del cólera',
                    'author' => 'Gabriel García Márquez',
                    'publisher' => 'Alfaguara',
                    'publication_year' => 1967,
                    'publication_place' => 'Madrid',
                    'edition_name' => 'Edición conmemorativa',
                    'page_count' => 471,
                    'dimensions' => '21 x 14 cm',
                    'weight' => 450,
                    'cover_image_url' => 'https://example.com/storage/covers/57.jpg',
                    'categories' => [
                        [
                            'id' => 1,
                            'name' => 'Novela',
                        ],
                    ],
                    'language' => 'es',
                    'binding_type' => 'paperback',
                    'condition' => 'good',
                    'defects' => ['underlining'],
                    'is_donation' => true,
                    'notes' => 'Algún subrayado a lápiz en los primeros capítulos.',
                    'photos' => [
                        [
                            'id' => 1,
                            'url' => 'https://example.com/storage/books/57/1.jpg',
                        ],
                    ],
                    'status' => 'available',
                    'distance_km' => 1.2,
                    'owner' => [
                        'id' => 3,
                        'username' => 'Ane',
                        'photo_url' => 'https://example.com/storage/photos/ane.jpg',
                        'description' => 'Lectora de novela y ensayo.',
                        'languages' => ['es', 'eu'],
                        'average_rating' => 4.6,
                        'ratings_count' => 5,
                        'exchanges_count' => 7,
                        'member_since' => '2026-01-15T10:00:00Z',
                        'blocked_by_me' => false,
                    ],
                    'is_mine' => false,
                    'created_at' => '2026-11-02T13:30:00Z',
                ],
                'requester' => [
                    'id' => 3,
                    'username' => 'Ane',
                    'photo_url' => 'https://example.com/storage/photos/ane.jpg',
                ],
                'owner' => [
                    'id' => 8,
                    'username' => 'Iker',
                    'photo_url' => 'https://example.com/storage/photos/iker.jpg',
                ],
                'message' => 'Hola, me interesa tu libro. ¿Te parece bien el cambio?',
                'counteroffer_made' => false,
                'delivery_confirmed_by_me' => false,
                'delivery_confirmed_by_other' => false,
                'chat_read_only' => false,
                'unread_messages' => 2,
                'can_rate' => false,
                'expires_at' => '2026-11-22T13:30:00Z',
                'created_at' => '2026-11-15T10:00:00Z',
                'updated_at' => '2026-11-15T10:00:00Z',
            ],
        ]);
    }

    /**
     * Hacer una contraoferta
     *
     * Solo en trueques y solo una vez por solicitud (RN-09). El propietario puede elegir otro libro
     * de la biblioteca del solicitante.
     */
    #[Response(status: 403, description: 'El usuario no puede hacer la contraoferta.', type: 'array{message: string}')]
    #[Response(status: 409, description: 'La operación incumple una regla de negocio.', type: 'array{message: string, code: string}')]
    public function counteroffer(Request $request, int $offer)
    {
        $request->validate([
            /** Libro del solicitante que pide el propietario. */
            'offered_book_id' => ['required', 'integer'],
        ]);

        return response()->json([
            'data' => [
                'id' => 15,
                'type' => 'trade',
                'status' => 'pending',
                'requested_book' => [
                    'id' => 42,
                    'isbn' => '9788420412146',
                    'title' => 'Cien años de soledad',
                    'author' => 'Gabriel García Márquez',
                    'publisher' => 'Alfaguara',
                    'publication_year' => 1967,
                    'publication_place' => 'Madrid',
                    'edition_name' => 'Edición conmemorativa',
                    'page_count' => 471,
                    'dimensions' => '21 x 14 cm',
                    'weight' => 450,
                    'cover_image_url' => 'https://example.com/storage/covers/42.jpg',
                    'categories' => [
                        [
                            'id' => 1,
                            'name' => 'Novela',
                        ],
                    ],
                    'language' => 'es',
                    'binding_type' => 'paperback',
                    'condition' => 'good',
                    'defects' => ['underlining'],
                    'is_donation' => true,
                    'notes' => 'Algún subrayado a lápiz en los primeros capítulos.',
                    'photos' => [
                        [
                            'id' => 1,
                            'url' => 'https://example.com/storage/books/42/1.jpg',
                        ],
                    ],
                    'status' => 'available',
                    'distance_km' => 1.2,
                    'owner' => [
                        'id' => 3,
                        'username' => 'Ane',
                        'photo_url' => 'https://example.com/storage/photos/ane.jpg',
                        'description' => 'Lectora de novela y ensayo.',
                        'languages' => ['es', 'eu'],
                        'average_rating' => 4.6,
                        'ratings_count' => 5,
                        'exchanges_count' => 7,
                        'member_since' => '2026-01-15T10:00:00Z',
                        'blocked_by_me' => false,
                    ],
                    'is_mine' => false,
                    'created_at' => '2026-11-02T13:30:00Z',
                ],
                'offered_book' => [
                    'id' => 57,
                    'isbn' => '9788497592451',
                    'title' => 'El amor en los tiempos del cólera',
                    'author' => 'Gabriel García Márquez',
                    'publisher' => 'Alfaguara',
                    'publication_year' => 1967,
                    'publication_place' => 'Madrid',
                    'edition_name' => 'Edición conmemorativa',
                    'page_count' => 471,
                    'dimensions' => '21 x 14 cm',
                    'weight' => 450,
                    'cover_image_url' => 'https://example.com/storage/covers/57.jpg',
                    'categories' => [
                        [
                            'id' => 1,
                            'name' => 'Novela',
                        ],
                    ],
                    'language' => 'es',
                    'binding_type' => 'paperback',
                    'condition' => 'good',
                    'defects' => ['underlining'],
                    'is_donation' => true,
                    'notes' => 'Algún subrayado a lápiz en los primeros capítulos.',
                    'photos' => [
                        [
                            'id' => 1,
                            'url' => 'https://example.com/storage/books/57/1.jpg',
                        ],
                    ],
                    'status' => 'available',
                    'distance_km' => 1.2,
                    'owner' => [
                        'id' => 3,
                        'username' => 'Ane',
                        'photo_url' => 'https://example.com/storage/photos/ane.jpg',
                        'description' => 'Lectora de novela y ensayo.',
                        'languages' => ['es', 'eu'],
                        'average_rating' => 4.6,
                        'ratings_count' => 5,
                        'exchanges_count' => 7,
                        'member_since' => '2026-01-15T10:00:00Z',
                        'blocked_by_me' => false,
                    ],
                    'is_mine' => false,
                    'created_at' => '2026-11-02T13:30:00Z',
                ],
                'requester' => [
                    'id' => 3,
                    'username' => 'Ane',
                    'photo_url' => 'https://example.com/storage/photos/ane.jpg',
                ],
                'owner' => [
                    'id' => 8,
                    'username' => 'Iker',
                    'photo_url' => 'https://example.com/storage/photos/iker.jpg',
                ],
                'message' => 'Hola, me interesa tu libro. ¿Te parece bien el cambio?',
                'counteroffer_made' => false,
                'delivery_confirmed_by_me' => false,
                'delivery_confirmed_by_other' => false,
                'chat_read_only' => false,
                'unread_messages' => 2,
                'can_rate' => false,
                'expires_at' => '2026-11-22T13:30:00Z',
                'created_at' => '2026-11-15T10:00:00Z',
                'updated_at' => '2026-11-15T10:00:00Z',
            ],
        ]);
    }

    /**
     * Cancelar una solicitud
     *
     * El solicitante cancela su solicitud que está en `pending` (RF-24), o cualquiera de las partes
     * cancela un intercambio `accepted` antes de que se complete.
     */
    #[Response(status: 403, description: 'El usuario no puede cancelar esta solicitud.', type: 'array{message: string}')]
    #[Response(status: 409, description: 'La operación incumple una regla de negocio.', type: 'array{message: string, code: string}')]
    public function cancel(int $offer)
    {
        return response()->json([
            'data' => [
                'id' => 15,
                'type' => 'trade',
                'status' => 'pending',
                'requested_book' => [
                    'id' => 42,
                    'isbn' => '9788420412146',
                    'title' => 'Cien años de soledad',
                    'author' => 'Gabriel García Márquez',
                    'publisher' => 'Alfaguara',
                    'publication_year' => 1967,
                    'publication_place' => 'Madrid',
                    'edition_name' => 'Edición conmemorativa',
                    'page_count' => 471,
                    'dimensions' => '21 x 14 cm',
                    'weight' => 450,
                    'cover_image_url' => 'https://example.com/storage/covers/42.jpg',
                    'categories' => [
                        [
                            'id' => 1,
                            'name' => 'Novela',
                        ],
                    ],
                    'language' => 'es',
                    'binding_type' => 'paperback',
                    'condition' => 'good',
                    'defects' => ['underlining'],
                    'is_donation' => true,
                    'notes' => 'Algún subrayado a lápiz en los primeros capítulos.',
                    'photos' => [
                        [
                            'id' => 1,
                            'url' => 'https://example.com/storage/books/42/1.jpg',
                        ],
                    ],
                    'status' => 'available',
                    'distance_km' => 1.2,
                    'owner' => [
                        'id' => 3,
                        'username' => 'Ane',
                        'photo_url' => 'https://example.com/storage/photos/ane.jpg',
                        'description' => 'Lectora de novela y ensayo.',
                        'languages' => ['es', 'eu'],
                        'average_rating' => 4.6,
                        'ratings_count' => 5,
                        'exchanges_count' => 7,
                        'member_since' => '2026-01-15T10:00:00Z',
                        'blocked_by_me' => false,
                    ],
                    'is_mine' => false,
                    'created_at' => '2026-11-02T13:30:00Z',
                ],
                'offered_book' => [
                    'id' => 57,
                    'isbn' => '9788497592451',
                    'title' => 'El amor en los tiempos del cólera',
                    'author' => 'Gabriel García Márquez',
                    'publisher' => 'Alfaguara',
                    'publication_year' => 1967,
                    'publication_place' => 'Madrid',
                    'edition_name' => 'Edición conmemorativa',
                    'page_count' => 471,
                    'dimensions' => '21 x 14 cm',
                    'weight' => 450,
                    'cover_image_url' => 'https://example.com/storage/covers/57.jpg',
                    'categories' => [
                        [
                            'id' => 1,
                            'name' => 'Novela',
                        ],
                    ],
                    'language' => 'es',
                    'binding_type' => 'paperback',
                    'condition' => 'good',
                    'defects' => ['underlining'],
                    'is_donation' => true,
                    'notes' => 'Algún subrayado a lápiz en los primeros capítulos.',
                    'photos' => [
                        [
                            'id' => 1,
                            'url' => 'https://example.com/storage/books/57/1.jpg',
                        ],
                    ],
                    'status' => 'available',
                    'distance_km' => 1.2,
                    'owner' => [
                        'id' => 3,
                        'username' => 'Ane',
                        'photo_url' => 'https://example.com/storage/photos/ane.jpg',
                        'description' => 'Lectora de novela y ensayo.',
                        'languages' => ['es', 'eu'],
                        'average_rating' => 4.6,
                        'ratings_count' => 5,
                        'exchanges_count' => 7,
                        'member_since' => '2026-01-15T10:00:00Z',
                        'blocked_by_me' => false,
                    ],
                    'is_mine' => false,
                    'created_at' => '2026-11-02T13:30:00Z',
                ],
                'requester' => [
                    'id' => 3,
                    'username' => 'Ane',
                    'photo_url' => 'https://example.com/storage/photos/ane.jpg',
                ],
                'owner' => [
                    'id' => 8,
                    'username' => 'Iker',
                    'photo_url' => 'https://example.com/storage/photos/iker.jpg',
                ],
                'message' => 'Hola, me interesa tu libro. ¿Te parece bien el cambio?',
                'counteroffer_made' => false,
                'delivery_confirmed_by_me' => false,
                'delivery_confirmed_by_other' => false,
                'chat_read_only' => false,
                'unread_messages' => 2,
                'can_rate' => false,
                'expires_at' => '2026-11-22T13:30:00Z',
                'created_at' => '2026-11-15T10:00:00Z',
                'updated_at' => '2026-11-15T10:00:00Z',
            ],
        ]);
    }

    /**
     * Confirmar la entrega
     *
     * RF-25, RN-11. Cuando confirman las dos partes, la solicitud pasa a `completed`. Si solo
     * confirma uno de los dos, una tarea programada completa la solicitud igual a los 3 días.
     */
    #[Response(status: 403, description: 'El usuario no es parte de la solicitud.', type: 'array{message: string}')]
    #[Response(status: 409, description: 'La operación incumple una regla de negocio.', type: 'array{message: string, code: string}')]
    public function confirmDelivery(int $offer)
    {
        return response()->json([
            'data' => [
                'id' => 15,
                'type' => 'trade',
                'status' => 'pending',
                'requested_book' => [
                    'id' => 42,
                    'isbn' => '9788420412146',
                    'title' => 'Cien años de soledad',
                    'author' => 'Gabriel García Márquez',
                    'publisher' => 'Alfaguara',
                    'publication_year' => 1967,
                    'publication_place' => 'Madrid',
                    'edition_name' => 'Edición conmemorativa',
                    'page_count' => 471,
                    'dimensions' => '21 x 14 cm',
                    'weight' => 450,
                    'cover_image_url' => 'https://example.com/storage/covers/42.jpg',
                    'categories' => [
                        [
                            'id' => 1,
                            'name' => 'Novela',
                        ],
                    ],
                    'language' => 'es',
                    'binding_type' => 'paperback',
                    'condition' => 'good',
                    'defects' => ['underlining'],
                    'is_donation' => true,
                    'notes' => 'Algún subrayado a lápiz en los primeros capítulos.',
                    'photos' => [
                        [
                            'id' => 1,
                            'url' => 'https://example.com/storage/books/42/1.jpg',
                        ],
                    ],
                    'status' => 'available',
                    'distance_km' => 1.2,
                    'owner' => [
                        'id' => 3,
                        'username' => 'Ane',
                        'photo_url' => 'https://example.com/storage/photos/ane.jpg',
                        'description' => 'Lectora de novela y ensayo.',
                        'languages' => ['es', 'eu'],
                        'average_rating' => 4.6,
                        'ratings_count' => 5,
                        'exchanges_count' => 7,
                        'member_since' => '2026-01-15T10:00:00Z',
                        'blocked_by_me' => false,
                    ],
                    'is_mine' => false,
                    'created_at' => '2026-11-02T13:30:00Z',
                ],
                'offered_book' => [
                    'id' => 57,
                    'isbn' => '9788497592451',
                    'title' => 'El amor en los tiempos del cólera',
                    'author' => 'Gabriel García Márquez',
                    'publisher' => 'Alfaguara',
                    'publication_year' => 1967,
                    'publication_place' => 'Madrid',
                    'edition_name' => 'Edición conmemorativa',
                    'page_count' => 471,
                    'dimensions' => '21 x 14 cm',
                    'weight' => 450,
                    'cover_image_url' => 'https://example.com/storage/covers/57.jpg',
                    'categories' => [
                        [
                            'id' => 1,
                            'name' => 'Novela',
                        ],
                    ],
                    'language' => 'es',
                    'binding_type' => 'paperback',
                    'condition' => 'good',
                    'defects' => ['underlining'],
                    'is_donation' => true,
                    'notes' => 'Algún subrayado a lápiz en los primeros capítulos.',
                    'photos' => [
                        [
                            'id' => 1,
                            'url' => 'https://example.com/storage/books/57/1.jpg',
                        ],
                    ],
                    'status' => 'available',
                    'distance_km' => 1.2,
                    'owner' => [
                        'id' => 3,
                        'username' => 'Ane',
                        'photo_url' => 'https://example.com/storage/photos/ane.jpg',
                        'description' => 'Lectora de novela y ensayo.',
                        'languages' => ['es', 'eu'],
                        'average_rating' => 4.6,
                        'ratings_count' => 5,
                        'exchanges_count' => 7,
                        'member_since' => '2026-01-15T10:00:00Z',
                        'blocked_by_me' => false,
                    ],
                    'is_mine' => false,
                    'created_at' => '2026-11-02T13:30:00Z',
                ],
                'requester' => [
                    'id' => 3,
                    'username' => 'Ane',
                    'photo_url' => 'https://example.com/storage/photos/ane.jpg',
                ],
                'owner' => [
                    'id' => 8,
                    'username' => 'Iker',
                    'photo_url' => 'https://example.com/storage/photos/iker.jpg',
                ],
                'message' => 'Hola, me interesa tu libro. ¿Te parece bien el cambio?',
                'counteroffer_made' => false,
                'delivery_confirmed_by_me' => false,
                'delivery_confirmed_by_other' => false,
                'chat_read_only' => false,
                'unread_messages' => 2,
                'can_rate' => false,
                'expires_at' => '2026-11-22T13:30:00Z',
                'created_at' => '2026-11-15T10:00:00Z',
                'updated_at' => '2026-11-15T10:00:00Z',
            ],
        ]);
    }
}

