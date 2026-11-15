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
#[Group('Users')]
class UserRatingController extends Controller
{
    /**
     * Ver las valoraciones publicadas de otros usuarios
     *
     * Solo las publicadas (RN-13).
     */
    #[Response(status: 404, description: 'El recurso no existe o no es visible para el usuario.', type: 'array{message: string}')]
    public function index(Request $request, int $user)
    {
        $request->validate([
            'page' => ['sometimes', 'integer', 'min:1'],
        ]);

        return response()->json([
            'data' => [
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
            'links' => [
                'first' => 'http://localhost:8000/api/users/1/ratings?page=1',
                'last' => 'http://localhost:8000/api/users/1/ratings?page=5',
                'prev' => 'http://localhost:8000/api/users/1/ratings?page=1',
                'next' => 'http://localhost:8000/api/users/1/ratings?page=3',
            ],
            'meta' => [
                'current_page' => 1,
                'last_page' => 5,
                'per_page' => 20,
                'total' => 93,
            ],
        ]);
    }
}
