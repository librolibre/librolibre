<?php

namespace App\Http\Controllers\Api\Profile;

use App\Http\Controllers\Controller;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\Response;
use Illuminate\Http\Request;

/**
 * STUB: todos los métodos devuelven datos fijos. No hay lógica ni base de datos.
 * Los parámetros de ruta son escalares (no modelos) a propósito: con route model binding
 * Laravel iría a la base de datos y devolvería 404.
 */
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

    /**
     * Editar mi perfil
     *
     * RF-04. Solo se cambian los campos enviados. Las coordenadas se redondean antes de guardarse.
     */
    public function update(Request $request)
    {
        $request->validate([
            'username' => ['sometimes', 'string', 'min:2', 'max:60'],
            'description' => ['nullable', 'string', 'max:300'],
            'languages' => ['sometimes', 'array'],
            'languages.*' => ['string', 'min:2', 'max:2'],
            'location' => ['nullable', 'array'],
            'location.latitude' => ['required_with:location', 'numeric'],
            'location.longitude' => ['required_with:location', 'numeric'],
            'postal_code' => ['nullable', 'string', 'regex:/^[0-9]{5}$/'],
        ]);

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
                'email' => 'ane@example.com',
                'location' => [
                    'latitude' => 42.85,
                    'longitude' => -2.67,
                ],
                'role' => 'user',
                'privacy_accepted' => false,
                'postal_code' => '01005',
            ],
        ]);
    }

    /**
     * Eliminar mi cuenta
     *
     * RF-05, RN-16. Con esto se cancelan todas las solicitudes en curso, se avisa a la(s) otra(s) parte(s)
     * y
     * se retiran los libros. La confirmación («¿Seguro?») la pide el front end.
     */
    public function destroy()
    {
        return response()->noContent();
    }

    /**
     * Aceptar la política de privacidad
     *
     * Primer acceso (Si es la primera vez que accede con Google)
     */
    public function acceptPrivacyConsent()
    {
        return response()->noContent();
    }

    /**
     * Cambiar mi foto de perfil
     */
    public function updatePhoto(Request $request)
    {
        $request->validate([
            /** JPG o PNG, máximo 2 MB. */
            'photo' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ]);

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
                'email' => 'ane@example.com',
                'location' => [
                    'latitude' => 42.85,
                    'longitude' => -2.67,
                ],
                'role' => 'user',
                'privacy_accepted' => false,
                'postal_code' => '01005',
            ],
        ]);
    }
}
