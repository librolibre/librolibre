<?php

namespace Database\Factories;

use App\Models\AdminAction;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AdminAction>
 */
class AdminActionFactory extends Factory
{
    /**
     * Define the model's default state: an admin suspends a user.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'admin_id' => User::factory()->state([
                'profile_id' => Profile::firstOrCreate(['code' => 'admin'], ['name' => 'Administrador'])->id,
            ]),
            'action' => 'suspend_user',
            'target_type' => 'user',
            'target_id' => User::factory(),
            'reason' => fake()->sentence(),
        ];
    }

    /** Done by the system (3 strikes), not by an admin. */
    public function automatic(): static
    {
        return $this->state(fn (array $attributes) => ['admin_id' => null]);
    }
}
