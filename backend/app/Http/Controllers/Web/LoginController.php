<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

/**
 * The web route named "login" (routes/web.php). Laravel sends browsers without a session there.
 * The login screen is in Angular («Entrar con Google»), so this only redirects to the front end.
 * API requests (Angular, JSON) never come here: without a token they get a JSON 401.
 */
class LoginController extends Controller
{
    // Esto causaba un 500 en GET /login
    // die("hola");

    public function index(): RedirectResponse
    {
        // Redirigir a FRONTEND_URL - que es el index de Angular.
        // Si no sale lo esperado:
        // 1. Es que la configuración de las páginas en Angular está mal
        // o 2. simplemente no existe lo que debe existir
        // Para cambiar donde se redirige index, cambiar FRONTEND_URL en el .env
        return redirect()->away(config('app.frontend_url'));
    }
}
