<?php

namespace Database\Factories;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
// use Illuminate\Support\Facades\Hash;
// use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * We don't use passwords.
     */
    // protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'username' => fake()->unique()->userName(),
            'google_id' => fake()->unique()->numerify('#####################'),
            'profile_id' => Profile::firstOrCreate(['code' => 'user'], ['name' => 'Usuario'])->id,
            'email' => fake()->unique()->safeEmail(),
            // We don't use passwords
            //'password' => static::$password ??= Hash::make('password'),
        ];
    }

    /**
     * Deprecado:Indicate that the model's email address should be unverified.
     */
    // public function unverified(): static
    // {
    //     return $this->state(fn (array $attributes) => [
    //         'email_verified_at' => null,
    //     ]);
    // }
}
