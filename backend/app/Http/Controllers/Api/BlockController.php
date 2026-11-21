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
#[Group('Blocks')]
class BlockController extends Controller
{
    /**
     * Mostrar una lista de los usuarios que han sido bloqueados por el usuario
     */
    public function index()
    {
        return response()->json([
            'data' => [
                [
                    'id' => 3,
                    'username' => 'Ane',
                    'photo_url' => 'https://example.com/storage/photo_url.jpg',
                ],
            ],
        ]);
    }

    /**
     * Bloquear a un usuario
     *
     * RF-32, RN-15. Cancela las solicitudes pendientes entre los dos.
     */
    #[Response(status: 409, description: 'La operación incumple una regla de negocio.', type: 'array{message: string, code: string}')]
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => ['required', 'integer'],
        ]);

        return response()->noContent();
    }

    /**
     * Desbloquear a un usuario
     */
    #[Response(status: 404, description: 'El recurso no existe o no es visible para el usuario.', type: 'array{message: string}')]
    public function destroy(int $user)
    {
        return response()->noContent();
    }
}
