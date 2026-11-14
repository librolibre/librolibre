<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Models\User;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Socialite;

#[Group('Auth')]
class GoogleAuthController extends Controller
{
    /**
     * Iniciar el acceso con Google
     *
     * Redirige al usuario a la pantalla de consentimiento de Google (Socialite).
     *
     * @unauthenticated
     */
    public function redirect(): RedirectResponse
    {
        /** @var \Laravel\Socialite\Two\GoogleProvider $google */
        $google = Socialite::driver('google');

        return $google->stateless()->redirect();
    }

    /**
     * Volver de Google
     *
     * Google redirige aquí después de que el usuario dé su consentimiento. El servidor crea la cuenta
     * si es la primera vez y redirige al front end con un **código de un solo uso** (válido 60 s),
     * para que el token no quede en el historial ni en los logs:
     *
     * - Éxito: `{FRONTEND_URL}/auth/callback?code=<código>`
     * - Cancelado en Google: `{FRONTEND_URL}/?error=cancelled`
     * - Cuenta suspendida: `{FRONTEND_URL}/?error=suspended`
     *
     * El front end canjea el código por el token con `POST /auth/token`.
     *
     * @unauthenticated
     */
    public function callback(Request $request): RedirectResponse
    {
        if ($request->has('error')) {
            return redirect()->away(config('app.frontend_url').'/?error=cancelled');
        }

        /** @var \Laravel\Socialite\Two\GoogleProvider $google */
        $google = Socialite::driver('google');
        /** @var \Laravel\Socialite\Two\User $googleUser */
        $googleUser = $google->stateless()->user();

        $user = User::firstOrNew(
            ['google_id' => $googleUser->getId()],
            [
                'email' => $googleUser->getEmail(),
                'username' => $googleUser->getRaw()['given_name'] ?? $googleUser->getName(),
            ]
        );

        if (! $user->exists) {
            $user->profile_id = Profile::where('code', 'user')->value('id');
            $user->save();
        }

        if ($user->status === 'suspended') {
            return redirect()->away(config('app.frontend_url').'/?error=suspended');
        }

        $code = Str::random(40);
        Cache::put('login-code:'.$code, $user->id, 60);

        return redirect()->away(config('app.frontend_url').'/auth/callback?code='.$code);
    }

    /**
     * Canjear el código de un solo uso por un token
     *
     * El código solo sirve una vez y caduca a los 60 segundos. Devuelve el token de Sanctum,
     * si el usuario es nuevo (aún no ha aceptado la política de privacidad) y sus datos.
     *
     * @unauthenticated
     */
    public function token(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string']
        ]);

        $userId = Cache::pull('login-code:' . $validated['code']);

        if ($userId === null) {
            throw ValidationException::withMessages(['code' => 'El código no es válido o ha caducado.']);
        }

        $user = User::findOrFail($userId);
        $token = $user->createToken('google-login')->plainTextToken;
        $isNewUser = $user->privacy_accepted_at === null;

        return response()->json([
            'token' => $token,
            'is_new_user' => $isNewUser,
            'user' => $user,
        ]);
    }
}
