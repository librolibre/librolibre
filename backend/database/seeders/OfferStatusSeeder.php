<?php

namespace Database\Seeders;

use App\Models\OfferStatus;
use Illuminate\Database\Seeder;

class OfferStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Same codes as OfferStatus in the API contract (request states in the functional analysis).
        OfferStatus::updateOrCreate(['code' => 'pending'],      ['name' => 'Pendiente']);
        OfferStatus::updateOrCreate(['code' => 'counteroffer'], ['name' => 'Contraoferta']);
        OfferStatus::updateOrCreate(['code' => 'accepted'],     ['name' => 'Aceptada']);
        OfferStatus::updateOrCreate(['code' => 'completed'],    ['name' => 'Completada']);
        OfferStatus::updateOrCreate(['code' => 'rejected'],     ['name' => 'Rechazada']);
        OfferStatus::updateOrCreate(['code' => 'cancelled'],    ['name' => 'Cancelada']);
    }
}
