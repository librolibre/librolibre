<?php

namespace Tests\Feature\Web;

use Tests\TestCase;

/**
 * The web route named "login" (routes/web.php). Laravel sends browsers without a session there,
 * but the login screen lives in Angular, so it only redirects to the front end.
 */
class LoginRouteTest extends TestCase
{
    /** GET /login sends the browser to the front end (FRONTEND_URL), where «Entrar con Google» is. */
    public function test_login_redirects_to_the_front_end(): void
    {
        $this->get('/login')->assertRedirect(config('app.frontend_url'));
    }

    /** A browser request (no "Accept: application/json") to a protected API route still gets a JSON 401, not a redirect. */
    public function test_api_request_without_token_from_a_browser_gets_401(): void
    {
        $response = $this->get('/api/me');

        $response->assertUnauthorized();
        $response->assertJsonStructure(['message']);
    }
}
