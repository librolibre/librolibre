<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\Response;

/**
 * STUB: todos los métodos devuelven datos fijos. No hay lógica ni base de datos.
 * Los parámetros de ruta son escalares (no modelos) a propósito: con route model binding
 * Laravel iría a la base de datos y devolvería 404.
 */
#[Group('Reference data')]
class LanguageController extends Controller
{
    /**
     * Mostrar lista de los idiomas
     *
     * Para el idioma del libro y los idiomas de lectura del perfil, no para la localización de la app.
     */
    public function index()
    {
        return response()->json([
            'data' => [
                [
                    'code' => 'es',
                    'name' => 'Castellano',
                ],
            ],
        ]);
    }
}
