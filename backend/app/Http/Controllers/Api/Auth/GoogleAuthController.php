<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Models\User;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
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
    public function redirect()
    {
        /** @var \Laravel\Socialite\Two\GoogleProvider $google */
        $google = Socialite::driver('google');

        return $google->stateless()->redirect();
    }

    /**
     * Volver de Google
     *
     * Crea la cuenta si es la primera vez y redirige al front end con un código de un solo uso (60 s).
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
}
