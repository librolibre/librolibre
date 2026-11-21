<?php

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Language::updateOrCreate(['code' => 'es'], ['name' => 'Español']);
        Language::updateOrCreate(['code' => 'eu'], ['name' => 'Euskera']);
        Language::updateOrCreate(['code' => 'en'], ['name' => 'Inglés']);
        Language::updateOrCreate(['code' => 'fr'], ['name' => 'Francés']);
        Language::updateOrCreate(['code' => 'ca'], ['name' => 'Catalán']);
        Language::updateOrCreate(['code' => 'gl'], ['name' => 'Gallego']);
        Language::updateOrCreate(['code' => 'de'], ['name' => 'Alemán']);
        Language::updateOrCreate(['code' => 'pt'], ['name' => 'Portugués']);
        Language::updateOrCreate(['code' => 'it'], ['name' => 'Italiano']);
    }
}
