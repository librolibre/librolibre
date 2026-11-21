<?php

namespace Database\Factories;

use App\Models\Message;
use App\Models\Offer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Message>
 */
class MessageFactory extends Factory
{
    /**
     * Define the model's default state: a text message from the requester.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'offer_id' => Offer::factory(),
            'user_sender_id' => fn (array $attributes) => Offer::find($attributes['offer_id'])->requester_id,
            'type' => 'text',
            'body' => fake()->sentence(),
        ];
    }

    /** A system card (no sender, no text). */
    public function card(string $type, array $payload = []): static
    {
        return $this->state(fn (array $attributes) => [
            'user_sender_id' => null,
            'type' => $type,
            'body' => null,
            'payload' => $payload,
        ]);
    }
}
