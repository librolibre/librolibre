<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\OwnUserResource;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Controllers\Controller;
use App\Services\GeocodingService;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\Response;
use Illuminate\Http\Request;
// Aliased: "Response" is already Scramble's attribute above
use Illuminate\Http\Response as HttpResponse;

/**
 * STUB: update, destroy y updatePhoto devuelven datos fijos. No hay lógica ni base de datos.
 * show y acceptPrivacyConsent ya están programados (#27).
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
    public function update(UpdateProfileRequest $request, GeocodingService $geocoding): OwnUserResource
    {
        $user = $request->user();
        $data = $request->validated(); // only the keys that were sent AND passed validation

        // TODO 1. Location: decide the coordinates BEFORE saving anything
        //   if 'location' was sent (array_key_exists: it may be null to clear it):
        //       latitude/longitude = round(..., 2)   (or null if location is null)
        //   elseif 'postal_code' was sent and is not null:
        //       $coords = $geocoding->coordinatesFor($data['postal_code'])
        //       if $coords === null -> throw ValidationException::withMessages(['postal_code' => '...'])
        //       latitude/longitude = round($coords..., 2)
        //   (no location and no postal code -> leave the coordinates alone)
        //   Throw BEFORE save(): the "not found" test checks that nothing was written.

        // TODO 2. Simple fields: fill only the keys present
        //   Arr::only($data, ['username', 'description', 'postal_code'])
        //   + latitude/longitude from step 1, if you decided them

        // TODO 3. Save

        // TODO 4. Languages, only if the 'languages' key was sent
        //   codes -> ids: Language::whereIn('code', ...)->pluck('id')
        //   $user->languages()->sync($ids)

        // TODO 5. Return the fresh user (fresh() or load('languages'))
        return new OwnUserResource($user);
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
     * Primer acceso (Si es la primera vez que accede con Google). Guarda la fecha y hora de la aceptación;
     * si ya la había aceptado, se mantiene la primera fecha.
     */
    public function acceptPrivacyConsent(Request $request): HttpResponse
    {
        $user = $request->user();

        // The first date is kept: it proves when consent was given
        if ($user->privacy_accepted_at === null) {
            $user->privacy_accepted_at = now();
            $user->save();
        }

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
