<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PublicUserResource;
use App\Models\User;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\Response;
use Illuminate\Http\Request;

/**
 * Public profiles of other users. The route uses auth.optional: visitors and logged-in users can both see them.
 */
#[Group('Users')]
class UserController extends Controller
{
    /**
     * Ver el perfil público de un usuario
     *
     * RF-18. Nunca incluye email ni ubicación.
     */
    #[Response(status: 404, description: 'El recurso no existe o no es visible para el usuario.', type: 'array{message: string}')]
    public function show(User $user, Request $request): PublicUserResource
    {
        // Route model binding: Laravel finds the user by id, or answers 404 if it does not exist
        // The logged-in user looking at the profile, or null for a visitor (auth.optional)
        $viewer = $request->user();

        // Deleted accounts are anonymised, not removed (#29): hide them as if they did not exist
        abort_if($user->status === 'deleted', 404);

        // Whoever this user has blocked cannot see their profile (#73); a visitor cannot be blocked
        abort_if(
            $viewer !== null && $user->blockedUsers()->whereKey($viewer->id)->exists(),
            404,
        );

        return new PublicUserResource($user);
    }
}
