<?php

namespace App\Services;

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
        // TODO: Http::get('https://maps.googleapis.com/maps/api/geocode/json', [
        //     'components' => "postal_code:$postalCode|country:ES",
        //     'key'        => config('services.google_maps.key'),
        // ])
        //   - the test fake matches 'maps.googleapis.com/maps/api/geocode/*'
        //     and assertSent looks for the postal code in the URL (query string is fine)
        // TODO: if status !== 'OK' or results is empty -> return null
        // TODO: return results[0].geometry.location (lat, lng)

        return null;
    }
}
