<?php

namespace Database\Seeders;

use App\Models\ReportReason;
use Illuminate\Database\Seeder;

class ReportReasonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Same codes as ReportReason in the API contract; names from section 3.10 of the functional analysis v3.
        // Only "other" requires a description.
        ReportReason::updateOrCreate(['code' => 'harassment'],        ['name' => 'Acoso o insultos', 'requires_description' => false]);
        ReportReason::updateOrCreate(['code' => 'offensive_content'], ['name' => 'Contenido ofensivo o inapropiado', 'requires_description' => false]);
        ReportReason::updateOrCreate(['code' => 'spam'],              ['name' => 'Spam o publicidad', 'requires_description' => false]);
        ReportReason::updateOrCreate(['code' => 'sale_attempt'],      ['name' => 'Intento de venta o de cobro', 'requires_description' => false]);
        ReportReason::updateOrCreate(['code' => 'false_information'], ['name' => 'Información falsa o engañosa', 'requires_description' => false]);
        ReportReason::updateOrCreate(['code' => 'impersonation'],     ['name' => 'Suplantación de identidad', 'requires_description' => false]);
        ReportReason::updateOrCreate(['code' => 'no_show'],           ['name' => 'No se presentó al intercambio acordado', 'requires_description' => false]);
        ReportReason::updateOrCreate(['code' => 'other'],             ['name' => 'Otro', 'requires_description' => true]);
    }
}
