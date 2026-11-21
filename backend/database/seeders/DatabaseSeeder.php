<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(ProfileSeeder::class);
        $this->call(LanguageSeeder::class);
        $this->call(CategorySeeder::class);
        $this->call(DefectSeeder::class);
        $this->call(BindingTypeSeeder::class);
        $this->call(BookConditionSeeder::class);
        $this->call(OfferStatusSeeder::class);
        $this->call(ReportReasonSeeder::class);

        // Fake user only on your machine, never on Neon (dev/production)
        if (app()->isLocal()) {
            User::factory()->create([
                'username' => 'testuser',
                'email' => 'test@example.com',
            ]);
        }
    }
}
