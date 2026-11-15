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
class CategoryController extends Controller
{
    /**
     * Mostrar lista de las categorías (géneros)
     */
    public function index()
    {
        return response()->json([
            'data' => [
                [
                    'id' => 1,
                    'name' => 'Novela',
                ],
            ],
        ]);
    }
}
