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
#[Group('Ratings')]
class RatingController extends Controller
{
    /**
     * Valorar a la otra parte
     *
     * Solo en solicitudes `completed`, una vez por parte (RN-12). Se publica cuando hagan la valoración
     * las dos (RN-13).
     */
    #[Response(status: 403, description: 'El usuario no tiene permiso para esta acción.', type: 'array{message: string}')]
    #[Response(status: 409, description: 'La operación incumple una regla de negocio.', type: 'array{message: string, code: string}')]
    public function store(Request $request, int $offer)
    {
        $request->validate([
            'score' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:500'],
        ]);

        /** @status 201 */
        return response()->json([
            'data' => [
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
        ]);
    }
}
