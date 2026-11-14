<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Dedoc\Scramble\Attributes\Group;
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
}
