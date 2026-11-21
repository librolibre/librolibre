<?php

namespace Tests\Feature\Models;

use App\Models\Offer;
use App\Models\Rating;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RatingTest extends TestCase
{
    use RefreshDatabase;

    public static function validScores(): array
    {
        return ['lowest' => [1], 'highest' => [5]];
    }

    public static function invalidScores(): array
    {
        return ['zero' => [0], 'six' => [6]];
    }

    /** Scores from 1 to 5 are accepted. */
    #[DataProvider('validScores')]
    public function test_score_between_1_and_5_is_saved(int $score): void
    {
        $rating = Rating::factory()->create(['score' => $score])->fresh();

        $this->assertSame($score, $rating->score);
    }

    /** The CHECK constraint rejects anything outside 1–5. */
    #[DataProvider('invalidScores')]
    public function test_score_outside_1_to_5_is_rejected(int $score): void
    {
        $this->expectException(QueryException::class);

        Rating::factory()->create(['score' => $score]);
    }

    /** Each party can rate the other only once per offer (RN-12). */
    public function test_user_cannot_rate_the_same_offer_twice(): void
    {
        $rating = Rating::factory()->create();

        $this->expectException(UniqueConstraintViolationException::class);

        Rating::factory()->create([
            'offer_id' => $rating->offer_id,
            'user_rater_id' => $rating->user_rater_id,
        ]);
    }

    /** Both parties can rate the same offer. */
    public function test_both_parties_can_rate_the_same_offer(): void
    {
        $first = Rating::factory()->create();

        Rating::factory()->create([
            'offer_id' => $first->offer_id,
            'user_rater_id' => $first->user_rated_id,
            'user_rated_id' => $first->user_rater_id,
        ]);

        $this->assertSame(2, Rating::where('offer_id', $first->offer_id)->count());
    }

    // ------------------------------------------------------------------
    // Relationships
    // ------------------------------------------------------------------

    public function test_rating_belongs_to_an_offer_rater_and_rated_user(): void
    {
        $offer = Offer::factory()->create();
        $rating = Rating::factory()->create(['offer_id' => $offer->id]);

        $this->assertTrue($rating->offer->is($offer));
        $this->assertTrue($offer->ratings->contains($rating));
        $this->assertTrue($rating->rater->is($offer->requester));
        $this->assertTrue($rating->rated->is($offer->requestedBook->user));
    }
}
