<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\OwnUserResource;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Services\GeocodingService;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
// Aliased: "Response" is already Scramble's attribute above
use Illuminate\Http\Response as HttpResponse;

/**
 * STUB: destroy devuelve datos fijos (se programa en #29).
 * show, update, acceptPrivacyConsent y updatePhoto ya están programados (#27).
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
     * Con `postal_code` y sin `location`, las coordenadas se calculan con Google Maps; si no encuentra
     * el código postal, devuelve `422` y no se guarda nada.
     */
    public function update(UpdateProfileRequest $request, GeocodingService $geocoding): OwnUserResource
    {
        $user = $request->user();
        $data = $request->validated(); // only the keys that were sent AND passed validation

        // 1. Location, decided before saving anything (a 422 must leave the user untouched)
        if (array_key_exists('location', $data)) {
            // Sent by the front end (null clears it); Google Maps is not called
            $user->latitude = $data['location'] === null ? null : round((float) $data['location']['latitude'], 2);
            $user->longitude = $data['location'] === null ? null : round((float) $data['location']['longitude'], 2);
        } elseif (isset($data['postal_code'])) {
            $coordinates = $geocoding->coordinatesFor($data['postal_code']);

            if ($coordinates === null) {
                throw ValidationException::withMessages(['postal_code' => 'No encontramos ese código postal.']);
            }

            $user->latitude = round($coordinates['lat'], 2);
            $user->longitude = round($coordinates['lng'], 2);
        }

        // 2. Simple fields, only the keys that were sent (status, strikes and role are never here)
        $user->fill(Arr::only($data, ['username', 'description', 'postal_code']));

        // 3. Save
        $user->save();

        // 4. Languages, only if the key was sent: the codes replace the previous list
        if (array_key_exists('languages', $data)) {
            $user->languages()->sync(Language::whereIn('code', $data['languages'])->pluck('id'));
        }

        // 5. Fresh copy, so the response includes the new languages
        return new OwnUserResource($user->fresh());
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
     *
     * Guarda la foto (JPG o PNG, máximo 2 MB) y devuelve el perfil con la nueva `photo_url`.
     * La foto anterior se borra.
     */
    public function updatePhoto(Request $request): OwnUserResource
    {
        $request->validate([
            /** JPG o PNG, máximo 2 MB. */
            'photo' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ]);

        // return response()->json([
        //     'data' => [
        //         'id' => 3,
        //         'username' => 'Ane',
        //         'photo_url' => 'https://example.com/storage/photo_url.jpg',
        //         'description' => 'Lectora de novela y ensayo.',
        //         'languages' => ['es', 'eu'],
        //         'average_rating' => 4.6,
        //         'ratings_count' => 5,
        //         'exchanges_count' => 7,
        //         'member_since' => '2026-11-15T10:00:00Z',
        //         'blocked_by_me' => false,
        //         'email' => 'ane@example.com',
        //         'location' => [
        //             'latitude' => 42.85,
        //             'longitude' => -2.67,
        //         ],
        //         'role' => 'user',
        //         'privacy_accepted' => false,
        //         'postal_code' => '01005',
        //     ],
        // ]);

        $user = $request->user();
        // The photos disk comes from config (public locally, a bucket in production)
        $disk = config('filesystems.photos_disk');
        $oldPath = $user->photo_path;

        // store() gives the file a random name and returns its path; only the path is saved
        $user->photo_path = $request->file('photo')->store('profile-photos', $disk);
        $user->save();

        // The old file is deleted only after the new one is saved, so a failed upload keeps the old photo
        if ($oldPath !== null) {
            Storage::disk($disk)->delete($oldPath);
        }

        return new OwnUserResource($user->fresh());
    }
}
