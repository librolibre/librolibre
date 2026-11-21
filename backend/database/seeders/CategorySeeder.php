<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Category::updateOrCreate(['code' => 'novel'],           ['name' => 'Novela']);
        Category::updateOrCreate(['code' => 'poetry'],          ['name' => 'Poesía']);
        Category::updateOrCreate(['code' => 'essay'],           ['name' => 'Ensayo']);
        Category::updateOrCreate(['code' => 'children'],        ['name' => 'Infantil']);
        Category::updateOrCreate(['code' => 'young_adult'],     ['name' => 'Juvenil']);
        Category::updateOrCreate(['code' => 'comic'],           ['name' => 'Cómic']);
        Category::updateOrCreate(['code' => 'science_fiction'], ['name' => 'Ciencia ficción']);
        Category::updateOrCreate(['code' => 'fantasy'],         ['name' => 'Fantasía']);
        Category::updateOrCreate(['code' => 'history'],         ['name' => 'Historia']);
        Category::updateOrCreate(['code' => 'biography'],       ['name' => 'Biografía']);
        Category::updateOrCreate(['code' => 'self_help'],       ['name' => 'Autoayuda']);
        Category::updateOrCreate(['code' => 'crime'],           ['name' => 'Novela negra']);
        Category::updateOrCreate(['code' => 'romance'],         ['name' => 'Romántica']);
        Category::updateOrCreate(['code' => 'horror'],          ['name' => 'Terror']);
        Category::updateOrCreate(['code' => 'textbook'],        ['name' => 'Libros de texto']);
        Category::updateOrCreate(['code' => 'cooking'],         ['name' => 'Cocina']);
        Category::updateOrCreate(['code' => 'other'],           ['name' => 'Otros']);
                
    }
}
