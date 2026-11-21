<?php

namespace Database\Factories;

use App\Models\Offer;
use App\Models\Profile;
use App\Models\ReportReason;
use App\Models\User;
use App\Models\UserReport;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserReport>
 */
class UserReportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'reporter_user_id' => User::factory(),
            'reported_user_id' => User::factory(),
            'reason_id' => ReportReason::firstOrCreate(['code' => 'spam'], ['name' => 'Spam o publicidad'])->id,
            'description' => fake()->optional()->sentence(),
        ];
    }

    /** Reported from an offer: the requester reports the owner of the requested book. */
    public function fromOffer(): static
    {
        return $this->state(function (array $attributes) {
            $offer = Offer::factory()->create();

            return [
                'offer_id' => $offer->id,
                'reporter_user_id' => $offer->requester_id,
                'reported_user_id' => $offer->requestedBook->user_id,
            ];
        });
    }

    /** Resolved by an admin: valid (adds a strike) or dismissed. */
    public function resolved(string $status = 'valid'): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => $status,
            'strike_added' => $status === 'valid',
            'resolved_by' => User::factory()->state([
                'profile_id' => Profile::firstOrCreate(['code' => 'admin'], ['name' => 'Administrador'])->id,
            ]),
            'resolution_note' => fake()->sentence(),
            'resolved_at' => now(),
        ]);
    }
}
