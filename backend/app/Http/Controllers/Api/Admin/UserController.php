<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\AdminUserResource;
use App\Models\User;
use App\Services\AccountService;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\Response;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * The admin panel's users. Only admins get here (the admin Gate, #32).
 * suspend and reactivate are implemented (#36), through AccountService; the other methods are still stubs with fixed data.
 * Stubs take scalar route parameters on purpose (route model binding would query the database and give 404);
 * implemented methods (and show) take the User model instead.
 */
#[Group('Admin')]
class UserController extends Controller
{
    /**
     * Mostrar lista de usuarios
     *
     * RF-34, CU-18. Solo administradores.
     */
    #[Response(status: 403, description: 'El usuario no tiene permiso para esta acción.', type: 'array{message: string}')]
    public function index(Request $request): AnonymousResourceCollection
    {
        $request->validate([
            /** Nombre o email. */
            'q' => ['sometimes', 'string'],
            'status' => ['sometimes', 'in:active,suspended'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ]);

        $users = User::query()
            ->with('profile')
            ->when($request->q, fn ($query, $q) => $query->where(function ($w) use ($q) {
                $w->where('username', 'like', "%{$q}%")
                ->orWhere('email', 'like', "%{$q}%");
        }))
        ->when($request->status, fn ($query, $status) => $query->where('status', $status))
        ->withCount('books')
        ->orderBy('username')
        ->paginate(20);

        return AdminUserResource::collection($users);
    }

    /**
     * Ver el detalle de un usuario
     *
     * Libros, intercambios, valoraciones, denuncias recibidas y strikes.
     */
    #[Response(status: 403, description: 'El usuario no tiene permiso para esta acción.', type: 'array{message: string}')]
    #[Response(status: 404, description: 'El recurso no existe o no es visible para el usuario.', type: 'array{message: string}')]
    public function show(User $user)
    {
        return response()->json([
            'data' => [
                'id' => 3,
                'username' => 'Ane',
                'photo_url' => 'https://example.com/storage/photo_url.jpg',
                'email' => 'ane@example.com',
                'status' => 'active',
                'role' => 'user',
                'created_at' => '2026-11-15T10:00:00Z',
                'books_count' => 3,
                'average_rating' => 1.5,
                'strikes' => 1,
                'books' => [
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
                'offers' => [
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
                        'offered_book' => [
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
                        'requester' => [
                            'id' => 3,
                            'username' => 'Ane',
                            'photo_url' => 'https://example.com/storage/photo_url.jpg',
                        ],
                        'owner' => [
                            'id' => 3,
                            'username' => 'Ane',
                            'photo_url' => 'https://example.com/storage/photo_url.jpg',
                        ],
                        'message' => 'Hola, me interesa tu libro.',
                        'counteroffer_made' => false,
                        'delivery_confirmed_by_me' => false,
                        'delivery_confirmed_by_other' => false,
                        'chat_read_only' => false,
                        'unread_messages' => 1,
                        'can_rate' => false,
                        'expires_at' => '2026-11-15T10:00:00Z',
                        'created_at' => '2026-11-15T10:00:00Z',
                        'updated_at' => '2026-11-15T10:00:00Z',
                    ],
                ],
                'ratings' => [
                    [
                        'id' => 1,
                        'offer_id' => 1,
                        'rater' => [
                            'id' => 3,
                            'username' => 'Ane',
                            'photo_url' => 'https://example.com/storage/photo_url.jpg',
                        ],
                        'score' => 1,
                        'comment' => 'Todo perfecto, muy amable.',
                        'is_published' => true,
                        'created_at' => '2026-11-15T10:00:00Z',
                    ],
                ],
                'reports_received' => [
                    [
                        'id' => 1,
                        'reporter' => [
                            'id' => 3,
                            'username' => 'Ane',
                            'photo_url' => 'https://example.com/storage/photo_url.jpg',
                        ],
                        'reported_user' => [
                            'id' => 3,
                            'username' => 'Ane',
                            'photo_url' => 'https://example.com/storage/photo_url.jpg',
                            'email' => 'ane@example.com',
                            'status' => 'active',
                            'role' => 'user',
                            'created_at' => '2026-11-15T10:00:00Z',
                            'books_count' => 3,
                            'average_rating' => 1.5,
                            'strikes' => 1,
                        ],
                        'reason' => 'harassment',
                        'description' => 'Lectora de novela y ensayo.',
                        'offer_id' => 1,
                        'status' => 'open',
                        'resolution' => [
                            'admin' => [
                                'id' => 3,
                                'username' => 'Ane',
                                'photo_url' => 'https://example.com/storage/photo_url.jpg',
                            ],
                            'reason' => 'Motivo de la decisión.',
                            'strike_added' => false,
                            'resolved_at' => '2026-11-15T10:00:00Z',
                        ],
                        'created_at' => '2026-11-15T10:00:00Z',
                    ],
                ],
                'actions' => [
                    [
                        'action' => 'suspend_user',
                        'admin' => [
                            'id' => 3,
                            'username' => 'Ane',
                            'photo_url' => 'https://example.com/storage/photo_url.jpg',
                        ],
                        'reason' => 'Motivo de la decisión.',
                        'created_at' => '2026-11-15T10:00:00Z',
                    ],
                ],
            ],
        ]);
    }

    /**
     * Suspender una cuenta
     *
     * RN-17. No se puede suspender a un admin, ni por supuesto a uno mismo (RN-19).
     */
    #[Response(status: 403, description: 'El usuario no tiene permiso para esta acción.', type: 'array{message: string}')]
    #[Response(status: 409, description: 'La operación incumple una regla de negocio.', type: 'array{message: string, code: string}')]
    public function suspend(AccountService $accounts, Request $request, User $user): AdminUserResource
    {
        $validated = $request->validate([
            /** Motivo de la decisión (queda registrado). */
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $accounts->suspend($user, $request->user(), $validated['reason']);

        return new AdminUserResource($user->fresh());
    }

    /**
     * Reactivar una cuenta
     *
     * RN-26. La cuenta vuelve a estar activa y el contador de strikes vuelve a cero; el historial de
     * denuncias y acciones se conserva. Solo se puede reactivar una cuenta suspendida (si no, `409`
     * con `INVALID_STATUS`). El usuario vuelve a entrar con Google: sus sesiones anteriores no se recuperan.
     */
    #[Response(status: 403, description: 'El usuario no tiene permiso para esta acción.', type: 'array{message: string}')]
    #[Response(status: 409, description: 'La operación incumple una regla de negocio.', type: 'array{message: string, code: string}')]
    public function reactivate(AccountService $accounts, Request $request, User $user): AdminUserResource
    {
        $validated = $request->validate([
            /** Motivo de la decisión (queda registrado). */
            'reason' => ['required', 'string', 'max:500'],
        ]);

        // return response()->json([
        //     'data' => [
        //         'id' => 3,
        //         'username' => 'Ane',
        //         'photo_url' => 'https://example.com/storage/photo_url.jpg',
        //         'email' => 'ane@example.com',
        //         'status' => 'active',
        //         'role' => 'user',
        //         'created_at' => '2026-11-15T10:00:00Z',
        //         'books_count' => 3,
        //         'average_rating' => 1.5,
        //         'strikes' => 1,
        //     ],
        // ]);
        $accounts->reactivate($user, $request->user(), $validated['reason']);

        return new AdminUserResource($user->fresh());
    }
}
