<?php

namespace Database\Seeders;

use App\Models\Defect;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DefectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Defect::updateOrCreate(['code' => 'underlining'],        ['name' => 'Subrayado o resaltado']);
        Defect::updateOrCreate(['code' => 'margin_notes'],       ['name' => 'Anotaciones en los márgenes']);
        Defect::updateOrCreate(['code' => 'doodles'],            ['name' => 'Garabatos o dibujos']);
        Defect::updateOrCreate(['code' => 'name_or_dedication'], ['name' => 'Nombre o dedicatoria escritos']);
        Defect::updateOrCreate(['code' => 'damaged_cover'],      ['name' => 'Cubierta dañada']);
        Defect::updateOrCreate(['code' => 'damaged_spine'],      ['name' => 'Lomo dañado o despegado']);
        Defect::updateOrCreate(['code' => 'folded_pages'],       ['name' => 'Páginas dobladas']);
        Defect::updateOrCreate(['code' => 'loose_pages'],        ['name' => 'Páginas sueltas, rotas o arrancadas']);
        Defect::updateOrCreate(['code' => 'water_damage'],       ['name' => 'Manchas o daños por humedad']);
        Defect::updateOrCreate(['code' => 'yellowed_pages'],     ['name' => 'Páginas amarillentas']);
        Defect::updateOrCreate(['code' => 'odor'],               ['name' => 'Olor (humo, humedad)']);
        Defect::updateOrCreate(['code' => 'child_use'],          ['name' => 'Señales de uso infantil']);
        Defect::updateOrCreate(['code' => 'missing_material'],   ['name' => 'Falta sobrecubierta o material adicional']);
        Defect::updateOrCreate(['code' => 'library_stamp'],      ['name' => 'Sello o etiqueta de biblioteca']);
    }
}
