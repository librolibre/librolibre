<?php

namespace Database\Factories;

use App\Models\BindingType;
use App\Models\Book;
use App\Models\BookCondition;
use App\Models\Language;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Book>
 */
class BookFactory extends Factory
{
    /**
     * Define the model's default state.
     * status and is_donation are left out so the DB defaults apply.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(3),
            'author' => fake()->name(),
            'isbn' => fake()->isbn13(),
            'language_id' => Language::firstOrCreate(['code' => 'es'], ['name' => 'Español'])->id,
            'binding_type_id' => BindingType::firstOrCreate(['code' => 'paperback'], ['name' => 'Tapa blanda'])->id,
            'condition_id' => BookCondition::firstOrCreate(['code' => 'good'], ['name' => 'Bueno'])->id,
            'publisher' => fake()->company(),
            'publication_year' => fake()->numberBetween(1950, 2026),
            'page_count' => fake()->numberBetween(80, 900),
        ];
    }

    /** «Trueque y donación». */
    public function donation(): static
    {
        return $this->state(fn (array $attributes) => ['is_donation' => true]);
    }

    public function status(string $status): static
    {
        return $this->state(fn (array $attributes) => ['status' => $status]);
    }
}
