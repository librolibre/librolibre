<?php

namespace Database\Factories;

use App\Models\Offer;
use App\Models\Rating;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Rating>
 */
class RatingFactory extends Factory
{
    /**
     * Define the model's default state: the requester rates the owner.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'offer_id' => Offer::factory(),
            'user_rater_id' => fn (array $attributes) => Offer::find($attributes['offer_id'])->requester_id,
            'user_rated_id' => fn (array $attributes) => Offer::find($attributes['offer_id'])->requestedBook->user_id,
            'score' => fake()->numberBetween(1, 5),
            'comment' => fake()->optional()->sentence(),
        ];
    }
}
