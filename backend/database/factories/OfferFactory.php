<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\Offer;
use App\Models\OfferStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Offer>
 */
class OfferFactory extends Factory
{
    /**
     * Define the model's default state: a pending trade.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => 'trade',
            'requester_id' => User::factory(),
            'book_requested_id' => Book::factory(),
            // The offered book belongs to the requester
            'book_offered_id' => fn (array $attributes) => Book::factory()->create(['user_id' => $attributes['requester_id']])->id,
            'offer_status_id' => OfferStatus::firstOrCreate(['code' => 'pending'], ['name' => 'Pendiente'])->id,
        ];
    }

    /** A donation request: no book offered in exchange. */
    public function donation(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'donation',
            'book_offered_id' => null,
        ]);
    }
}
