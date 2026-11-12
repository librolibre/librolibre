<?php

namespace Database\Seeders;

use App\Models\BindingType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BindingTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        BindingType::updateOrCreate(['code' => 'paperback'], ['name' => 'Tapa blanda']);
        BindingType::updateOrCreate(['code' => 'hardcover'], ['name' => 'Tapa dura']);
        BindingType::updateOrCreate(['code' => 'pocket'],    ['name' => 'Bolsillo']);
        BindingType::updateOrCreate(['code' => 'other'],     ['name' => 'Otro']);
    }
}
