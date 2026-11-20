<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

#[Group('Reference data')]
class CategoryController extends Controller
{
    /**
     * Mostrar lista de las categorías (géneros)
     *
     * Para clasificar los libros y filtrar el catálogo. Los libros se refieren a las categorías por
     * su `code`, igual que a los idiomas. Ordenadas por nombre.
     *
     * @unauthenticated
     */
    public function index(): AnonymousResourceCollection
    {
        // return response()->json([
        //     'data' => [
        //         [
        //             'id' => 1,
        //             'name' => 'Novela',
        //         ],
        //     ],
        // ]);
        return CategoryResource::collection(Category::orderBy('name')->get());
    }
}
