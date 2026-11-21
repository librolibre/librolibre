<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\LanguageResource;
use App\Models\Language;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

#[Group('Reference data')]
class LanguageController extends Controller
{
    /**
     * Mostrar lista de los idiomas
     *
     * Para el idioma del libro y los idiomas de lectura del perfil, no para la localización de la app.
     * Ordenados por nombre.
     *
     * @unauthenticated
     */
    public function index(): AnonymousResourceCollection
    {
        // return response()->json([
        //     'data' => [
        //         [
        //             'code' => 'es',
        //             'name' => 'Castellano',
        //         ],
        //     ],
        // ]);
        return LanguageResource::collection(Language::orderBy('name')->get());
    }
}
