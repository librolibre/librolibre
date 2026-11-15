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
#[Group('Chat')]
class MessageController extends Controller
{
    /**
     * Leer la conversación de una solicitud
     *
     * Mostrar los mensajes más recientes primero. Los nuevos llegan en tiempo real por Reverb (canal
     * privado `offer.{id}`).
     */
    #[Response(status: 403, description: 'El usuario no tiene permiso para esta acción.', type: 'array{message: string}')]
    public function index(Request $request, int $offer)
    {
        $request->validate([
            'page' => ['sometimes', 'integer', 'min:1'],
        ]);

        return response()->json([
            'data' => [
                [
                    'id' => 1,
                    'offer_id' => 1,
                    'type' => 'text',
                    'payload' => [
                        'offer_id' => 15,
                    ],
                    'sender' => [
                        'id' => 3,
                        'username' => 'Ane',
                        'photo_url' => 'https://example.com/storage/photo_url.jpg',
                    ],
                    'body' => 'Hola, ¿cuándo podemos quedar?',
                    'created_at' => '2026-11-15T10:00:00Z',
                ],
            ],
            'links' => [
                'first' => 'http://localhost:8000/api/offers/1/messages?page=1',
                'last' => 'http://localhost:8000/api/offers/1/messages?page=5',
                'prev' => 'http://localhost:8000/api/offers/1/messages?page=1',
                'next' => 'http://localhost:8000/api/offers/1/messages?page=3',
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
     * Enviar un mensaje
     *
     * No se permite si la conversación está en solo lectura (RN-14) o uno de los usuarios está bloqueado
     * (RN-15).
     */
    #[Response(status: 403, description: 'El usuario no tiene permiso para esta acción.', type: 'array{message: string}')]
    #[Response(status: 409, description: 'La operación incumple una regla de negocio.', type: 'array{message: string, code: string}')]
    public function store(Request $request, int $offer)
    {
        $request->validate([
            'body' => ['required', 'string', 'min:1', 'max:2000'],
        ]);

        /** @status 201 */
        return response()->json([
            'data' => [
                'id' => 1,
                'offer_id' => 1,
                'type' => 'text',
                'payload' => [
                    'offer_id' => 15,
                ],
                'sender' => [
                    'id' => 3,
                    'username' => 'Ane',
                    'photo_url' => 'https://example.com/storage/photo_url.jpg',
                ],
                'body' => 'Hola, ¿cuándo podemos quedar?',
                'created_at' => '2026-11-15T10:00:00Z',
            ],
        ]);
    }

    /**
     * Marcar la conversación como leída
     */
    #[Response(status: 403, description: 'El usuario no tiene permiso para esta acción.', type: 'array{message: string}')]
    public function markAsRead(int $offer)
    {
        return response()->noContent();
    }
}
