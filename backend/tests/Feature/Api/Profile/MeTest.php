<?php

namespace Tests\Feature\Api\Profile;

use App\Models\Book;
use App\Models\Language;
use App\Models\Offer;
use App\Models\OfferStatus;
use App\Models\Rating;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MeTest extends TestCase
{
    use RefreshDatabase;

    /** GET /me returns my profile with the fields of OwnUser in the contract. */
    public function test_me_returns_own_profile(): void
    {
        $user = User::factory()->create([
            'username' => 'Ane',
            'email' => 'ane@example.com',
            'postal_code' => '01005',
            'latitude' => 42.85,
            'longitude' => -2.67,
            'privacy_accepted_at' => now(),
        ]);
        $user->languages()->attach(Language::create(['code' => 'eu', 'name' => 'Euskera']));

        $response = $this->withToken($user->createToken('test')->plainTextToken)->getJson('/api/me');

        $response->assertOk();
        $response->assertJsonPath('data.id', $user->id);
        $response->assertJsonPath('data.username', 'Ane');
        $response->assertJsonPath('data.email', 'ane@example.com');
        $response->assertJsonPath('data.role', 'user');
        $response->assertJsonPath('data.privacy_accepted', true);
        $response->assertJsonPath('data.postal_code', '01005');
        $response->assertJsonPath('data.location', ['latitude' => 42.85, 'longitude' => -2.67]);
        $response->assertJsonPath('data.languages', ['eu']);
        $response->assertJsonPath('data.photo_url', null);
    }

    /** Moderation fields and google_id are internal: they never appear in the response. */
    public function test_me_hides_internal_fields(): void
    {
        $user = User::factory()->create();

        $response = $this->withToken($user->createToken('test')->plainTextToken)->getJson('/api/me');

        $response->assertOk();
        foreach (['status', 'strikes', 'profile_id', 'google_id', 'latitude', 'longitude', 'privacy_accepted_at'] as $field) {
            $response->assertJsonMissingPath('data.'.$field);
        }
    }

    /** Without coordinates there is no location, and a new user has not accepted the privacy policy. */
    public function test_me_location_is_null_without_coordinates(): void
    {
        $user = User::factory()->create(['latitude' => null, 'longitude' => null]);

        $response = $this->withToken($user->createToken('test')->plainTextToken)->getJson('/api/me');

        $response->assertJsonPath('data.location', null);
        $response->assertJsonPath('data.privacy_accepted', false);
    }

    /** Ratings received and completed exchanges are counted for the user. */
    public function test_me_includes_ratings_and_exchanges(): void
    {
        $user = User::factory()->create();
        $completed = OfferStatus::firstOrCreate(['code' => 'completed'], ['name' => 'Completada']);

        foreach ([4, 5] as $score) {
            $offer = Offer::factory()->create([
                'book_requested_id' => Book::factory()->create(['user_id' => $user->id])->id,
                'offer_status_id' => $completed->id,
            ]);
            Rating::factory()->create(['offer_id' => $offer->id, 'score' => $score]);
        }
        // A pending offer is not a completed exchange
        Offer::factory()->create(['book_requested_id' => Book::factory()->create(['user_id' => $user->id])->id]);

        $response = $this->withToken($user->createToken('test')->plainTextToken)->getJson('/api/me');

        $response->assertJsonPath('data.average_rating', 4.5);
        $response->assertJsonPath('data.ratings_count', 2);
        $response->assertJsonPath('data.exchanges_count', 2);
    }

    public function test_me_requires_a_token(): void
    {
        $this->getJson('/api/me')->assertUnauthorized();
    }
}
