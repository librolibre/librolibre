<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\AdminUserResource;
use App\Http\Resources\AdminUserDetailResource;
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
                $w->where('username', 'ilike', "%{$q}%")
                    ->orWhere('email', 'ilike', "%{$q}%");
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
        $user->load([
            'profile',
            'books' => fn ($q) => $q->with(['categories', 'photos', 'owner.profile'])->latest(),
            'offers' => fn ($q) => $q->with([
                'requestedBook.categories',
                'requestedBook.photos',
                'requestedBook.owner.profile',
                'offeredBook.categories',
                'offeredBook.photos',
                'offeredBook.owner.profile',
                'requester',
                'owner',
            ])->latest(),
            'ratingsReceived' => fn ($q) => $q->with('rater')->latest(),
            //'reportsReceived' => fn ($q) => $q->with([
            //    'reporter',
            //    'reportedUser.profile',
            //    'resolution.admin',
            //])->latest(),
            'adminActions' => fn ($q) => $q->with('admin')->latest(),
        ])->loadCount('books');

        return new AdminUserDetailResource($user);
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
