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
class IsbnController extends Controller
{
    /**
     * Buscar los datos de un libro por ISBN
     *
     * Consulta Open Library (con caché). Si el libro no existe u Open Library no responde,
     * devuelve `found: false` para que el usuario pueda rellenar los datos manualmente.
     */
    #[Response(status: 422, description: 'Error de validación.', type: 'array{message: string, errors: array}')]
    public function __invoke(string $isbn)
    {
        return response()->json([
            'found' => true,
            'isbn' => '9788420412146',
            'title' => 'Cien años de soledad',
            'author' => 'Gabriel García Márquez',
            'publisher' => 'Alfaguara',
            'publication_year' => 1967,
            'page_count' => 3,
            'cover_image_url' => 'https://example.com/storage/cover_image_url.jpg',
        ]);
    }
}
