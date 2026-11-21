<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

/**
 * Turns a Spanish postal code into coordinates with the Google Maps Geocoding API.
 * Laravel injects it into MeController::update by type-hint (no constructor needed).
 */
class GeocodingService
{
    /**
     * @return array{lat: float, lng: float}|null null when Google Maps cannot find the postal code
     */
    public function coordinatesFor(string $postalCode): ?array
    {
        $response = Http::get('https://maps.googleapis.com/maps/api/geocode/json', [
            'components' => "postal_code:$postalCode|country:ES",
            'key' => config('services.google_maps.key'),
        ]);

        // A network error, a wrong key (REQUEST_DENIED) or ZERO_RESULTS all mean "not found" for us
        if (! $response->successful() || $response->json('status') !== 'OK') {
            return null;
        }

        $location = $response->json('results.0.geometry.location');

        if (! isset($location['lat'], $location['lng'])) {
            return null;
        }

        return ['lat' => (float) $location['lat'], 'lng' => (float) $location['lng']];
    }
}
