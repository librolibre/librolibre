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
        // Same codes as ReportReason in the API contract. Only "other" requires a description.
        ReportReason::updateOrCreate(['code' => 'harassment'],        ['name' => 'Acoso', 'requires_description' => false]);
        ReportReason::updateOrCreate(['code' => 'offensive_content'], ['name' => 'Contenido ofensivo', 'requires_description' => false]);
        ReportReason::updateOrCreate(['code' => 'spam'],              ['name' => 'Spam', 'requires_description' => false]);
        ReportReason::updateOrCreate(['code' => 'sale_attempt'],      ['name' => 'Intento de venta', 'requires_description' => false]);
        ReportReason::updateOrCreate(['code' => 'false_information'], ['name' => 'Información falsa', 'requires_description' => false]);
        ReportReason::updateOrCreate(['code' => 'impersonation'],     ['name' => 'Suplantación de identidad', 'requires_description' => false]);
        ReportReason::updateOrCreate(['code' => 'no_show'],           ['name' => 'No se presentó a la entrega', 'requires_description' => false]);
        ReportReason::updateOrCreate(['code' => 'other'],             ['name' => 'Otro', 'requires_description' => true]);
    }
}
