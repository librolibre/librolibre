<?php

namespace App\Http\Controllers\Api\Admin;

use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\Response;
use Illuminate\Http\Request;

/**
 * STUB: todos los métodos devuelven datos fijos. No hay lógica ni base de datos.
 * Los parámetros de ruta son escalares (no modelos) a propósito: con route model binding
 * Laravel iría a la base de datos y devolvería 404.
 */
#[Group('Admin')]
class ReportController extends \App\Http\Controllers\Controller
{
    /**
     * Mostrar lista de denuncias
     *
     * RF-37, CU-20. Por defecto, las abiertas.
     */
    #[Response(status: 403, description: 'El usuario no tiene permiso para esta acción.', type: 'array{message: string}')]
    public function index(Request $request)
    {
        $request->validate([
            'status' => ['sometimes', 'in:open,valid,dismissed'],
            'reason' => ['sometimes', 'in:harassment,offensive_content,spam,sale_attempt,false_information,impersonation,no_show,other'],
            'reported_user_id' => ['sometimes', 'integer'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ]);

        return response()->json([
            'data' => [
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
            'links' => [
                'first' => 'http://localhost:8000/api/admin/reports?page=1',
                'last' => 'http://localhost:8000/api/admin/reports?page=5',
                'prev' => 'http://localhost:8000/api/admin/reports?page=1',
                'next' => 'http://localhost:8000/api/admin/reports?page=3',
            ],
            'meta' => [
                'current_page' => 1,
                'last_page' => 5,
                'per_page' => 20,
                'total' => 93,
            ],
        ]);
    }

    /**
     * Ver una denuncia
     */
    #[Response(status: 403, description: 'El usuario no tiene permiso para esta acción.', type: 'array{message: string}')]
    #[Response(status: 404, description: 'El recurso no existe o no es visible para el usuario.', type: 'array{message: string}')]
    public function show(int $report)
    {
        return response()->json([
            'data' => [
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
        ]);
    }

    /**
     * Marcar una denuncia como válida
     *
     * RF-39 a RF-41. Suma un strike al denunciado, salvo que ese denunciante ya le hubiera sumado
     * uno (RN-25). Con tres strikes, la cuenta se suspende automáticamente (RN-26).
     * Notifica al denunciado y al denunciante (RN-27).
     */
    #[Response(status: 403, description: 'El usuario no tiene permiso para esta acción.', type: 'array{message: string}')]
    #[Response(status: 409, description: 'La operación incumple una regla de negocio.', type: 'array{message: string, code: string}')]
    public function markAsValid(Request $request, int $report)
    {
        $request->validate([
            /** Motivo de la decisión (queda registrado). */
            'reason' => ['required', 'string', 'max:500'],
        ]);

        return response()->json([
            'data' => [
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
        ]);
    }

    /**
     * Descartar una denuncia
     */
    #[Response(status: 403, description: 'El usuario no tiene permiso para esta acción.', type: 'array{message: string}')]
    #[Response(status: 409, description: 'La operación incumple una regla de negocio.', type: 'array{message: string, code: string}')]
    public function dismiss(Request $request, int $report)
    {
        $request->validate([
            /** Motivo de la decisión (queda registrado). */
            'reason' => ['required', 'string', 'max:500'],
        ]);

        return response()->json([
            'data' => [
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
        ]);
    }
}
