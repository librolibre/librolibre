<?php

namespace Database\Seeders;

use App\Models\BookCondition;
use Illuminate\Database\Seeder;

class BookConditionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // From best to worst (same codes as BookCondition in the API contract).
        BookCondition::updateOrCreate(['code' => 'new'],        ['name' => 'Nuevo']);
        BookCondition::updateOrCreate(['code' => 'like_new'],   ['name' => 'Como nuevo']);
        BookCondition::updateOrCreate(['code' => 'very_good'],  ['name' => 'Muy bueno']);
        BookCondition::updateOrCreate(['code' => 'good'],       ['name' => 'Bueno']);
        BookCondition::updateOrCreate(['code' => 'acceptable'], ['name' => 'Aceptable']);
    }
}
