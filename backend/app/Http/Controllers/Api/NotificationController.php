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
#[Group('Notifications')]
class NotificationController extends Controller
{
    /**
     * Mostrar lista de mis notificaciones
     *
     * Ordenadas por fecha, las más recientes primero (CU-15).
     */
    public function index(Request $request)
    {
        $request->validate([
            'unread_only' => ['sometimes', 'boolean'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ]);

        return response()->json([
            'data' => [
                [
                    'id' => '9b2f3c1e-6a4d-4f0b-8c1a-2d5e7f9a1b3c',
                    'type' => 'offer_received',
                    'text' => 'Ane quiere tu libro «Cien años de soledad».',
                    'link' => [
                        'resource' => 'offer',
                        'id' => 1,
                    ],
                    'read_at' => '2026-11-15T10:00:00Z',
                    'created_at' => '2026-11-15T10:00:00Z',
                ],
            ],
            'links' => [
                'first' => 'http://localhost:8000/api/notifications?page=1',
                'last' => 'http://localhost:8000/api/notifications?page=5',
                'prev' => 'http://localhost:8000/api/notifications?page=1',
                'next' => 'http://localhost:8000/api/notifications?page=3',
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
     * Contador de las notificaciones sin leer
     *
     * Para el contador del menú.
     */
    public function unreadCount()
    {
        return response()->json([
            'count' => 3,
        ]);
    }

    /**
     * Marcar todas como leídas
     */
    public function markAllAsRead()
    {
        return response()->noContent();
    }

    /**
     * Marcar una notificación como leída
     */
    #[Response(status: 404, description: 'El recurso no existe o no es visible para el usuario.', type: 'array{message: string}')]
    public function markAsRead(string $notification)
    {
        return response()->noContent();
    }
}
