<?php

namespace Tests\Feature\Models;

use App\Models\Profile;
use Database\Seeders\ProfileSeeder;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    /** The seeder creates the two profiles the code relies on, looked up by code. */
    public function test_seeder_creates_user_and_admin_profiles(): void
    {
        $this->seed(ProfileSeeder::class);

        $this->assertSame(
            ['admin' => 'Administrador', 'user' => 'Usuario'],
            Profile::orderBy('code')->pluck('name', 'code')->all(),
        );
    }

    /** Running the seeder again (for example on Neon) must not duplicate rows. */
    public function test_profile_seeder_can_run_twice_without_duplicates(): void
    {
        $this->seed(ProfileSeeder::class);
        $this->seed(ProfileSeeder::class);

        $this->assertSame(2, Profile::count());
    }

    /** `code` is what the code compares against, so it can never repeat. */
    public function test_profile_code_must_be_unique(): void
    {
        Profile::create(['code' => 'user', 'name' => 'Usuario']);

        $this->expectException(UniqueConstraintViolationException::class);

        Profile::create(['code' => 'user', 'name' => 'Otro nombre']);
    }
}
