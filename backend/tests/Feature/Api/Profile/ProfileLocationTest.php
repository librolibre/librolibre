<?php

namespace Tests\Feature\Api\Profile;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ProfileLocationTest extends TestCase
{
    use RefreshDatabase;

    // ------------------------------------------------------------------
    // PATCH /me: location sent by the front end
    // ------------------------------------------------------------------

    public function test_coordinates_are_rounded_to_two_decimals(): void
    {
        $user = User::factory()->create();

        $response = $this->withToken($user->createToken('test')->plainTextToken)->patchJson('/api/me', [
            'location' => [
                'latitude' => 23.232323,
                'longitude' => 67.676767
            ]
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'latitude' => 23.23,
            'longitude' => 67.68
            ]);
    }

    public function test_negative_coordinates_are_rounded_to_two_decimals(): void
    {
        $user = User::factory()->create();

        $response = $this->withToken($user->createToken('test')->plainTextToken)->patchJson('/api/me', [
            'location' => [
                'latitude' => -23.232323,
                'longitude' => -67.676767
            ]
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'latitude' => -23.23,
            'longitude' => -67.68
            ]);
    }

    public function test_location_needs_longitude(): void
    {
        $user = User::factory()->create();

        $response = $this->withToken($user->createToken('test')->plainTextToken)->patchJson('/api/me', [
            'location' => [
                'latitude' => 23.23
            ]
        ]);

        $response->assertJsonValidationErrors(['location.longitude']);

        $response->assertUnprocessable();
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'latitude' => null,
            'longitude' => null
        ]);
    }

    public function test_location_needs_latitude(): void
    {
        $user = User::factory()->create();

        $response = $this->withToken($user->createToken('test')->plainTextToken)->patchJson('/api/me', [
            'location' => [
                'longitude' => 67.67
            ]
        ]);

        $response->assertJsonValidationErrors(['location.latitude']);

        $response->assertUnprocessable();
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'latitude' => null,
            'longitude' => null
        ]);
    }

    // ------------------------------------------------------------------
    // PATCH /me: postal code without location (Google Maps, faked)
    // ------------------------------------------------------------------

    /** With a postal code and no location, the coordinates come from Google Maps (faked) and are rounded. */
    public function test_postal_code_without_location_gets_coordinates_from_google_maps(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'maps.googleapis.com/maps/api/geocode/*' => Http::response([
                'status' => 'OK',
                'results' => [
                    ['geometry' => ['location' => ['lat' => 42.8467181, 'lng' => -2.6729903]]],
                ],
            ]),
        ]);
        $user = User::factory()->create();

        $response = $this->withToken($user->createToken('test')->plainTextToken)->patchJson('/api/me', [
            'postal_code' => '01005',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'postal_code' => '01005',
            'latitude' => 42.85,
            'longitude' => -2.67,
        ]);
        // Google Maps was asked about the postal code we sent
        Http::assertSent(fn ($request) => str_contains($request->url(), '01005'));
    }

    /** When the front end sends a location, that location is saved and Google Maps is not called. */
    public function test_location_sent_is_used_instead_of_google_maps(): void
    {
        Http::fake();
        $user = User::factory()->create();

        $response = $this->withToken($user->createToken('test')->plainTextToken)->patchJson('/api/me', [
            'postal_code' => '48001',
            'location' => [
                'latitude' => 43.263012,
                'longitude' => -2.934985,
            ],
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'postal_code' => '48001',
            'latitude' => 43.26,
            'longitude' => -2.93,
        ]);
        Http::assertNothingSent();
    }

    /** A postal code that Google Maps cannot find is rejected, and nothing is saved. */
    public function test_postal_code_not_found_by_google_maps(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'maps.googleapis.com/maps/api/geocode/*' => Http::response([
                'status' => 'ZERO_RESULTS',
                'results' => [],
            ]),
        ]);
        $user = User::factory()->create();

        $response = $this->withToken($user->createToken('test')->plainTextToken)->patchJson('/api/me', [
            'postal_code' => '00000',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['postal_code']);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'postal_code' => null,
            'latitude' => null,
            'longitude' => null,
        ]);
    }
}