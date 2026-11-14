<?php

namespace App\Http\Controllers\Api\Profile;

use App\Http\Controllers\Controller;
use App\Http\Resources\OwnUserResource;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Request;

#[Group('Profile')]
class MeController extends Controller
{
    /**
     * Obtener mi perfil
     *
     * Datos del usuario que ha iniciado sesión, incluidos el email, la zona y el rol.
     */
    public function show(Request $request): OwnUserResource
    {
        return new OwnUserResource($request->user());
    }
}
