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
#[Group('Reports')]
class ReportController extends Controller
{
    /**
     * Denunciar a un usuario
     *
     * RF-36, CU-17. Las denuncias se hacen sobre un usuario (no sobre libros, ni sobre intercambios).
     * `offer_id` es opcional:
     * indica desde qué solicitud o chat se ha denunciado, como contexto para el administrador.
     * Reglas: RN-21 (ni uno mismo ni un administrador), RN-22 (una abierta por persona).
     */
    #[Response(status: 409, description: 'La operación incumple una regla de negocio.', type: 'array{message: string, code: string}')]
    public function store(Request $request)
    {
        $request->validate([
            'reported_user_id' => ['required', 'integer'],
            'reason' => ['required', 'in:harassment,offensive_content,spam,sale_attempt,false_information,impersonation,no_show,other'],
            /** Obligatoria si el motivo es `other`. */
            'description' => ['nullable', 'string', 'max:1000'],
            /** Solicitud o chat desde el que se denuncia (contexto opcional). */
            'offer_id' => ['nullable', 'integer'],
        ]);

        /** @status 201 */
        return response()->json([
            'data' => [
                'id' => 1,
                'reported_user' => [
                    'id' => 3,
                    'username' => 'Ane',
                    'photo_url' => 'https://example.com/storage/photo_url.jpg',
                ],
                'reason' => 'harassment',
                'status' => 'open',
                'created_at' => '2026-11-15T10:00:00Z',
            ],
        ]);
    }
}
