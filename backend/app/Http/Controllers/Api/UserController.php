<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\Response;

/**
 * STUB: todos los métodos devuelven datos fijos. No hay lógica ni base de datos.
 * Los parámetros de ruta son escalares (no modelos) a propósito: con route model binding
 * Laravel iría a la base de datos y devolvería 404.
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
    public function show(int $user)
    {
        return response()->json([
            'data' => [
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
        ]);
    }
}
