<?php

namespace App\Services;

use App\Exceptions\StationLookupException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Sucht Tankstellen anhand einer deutschen Postleitzahl in OpenStreetMap.
 *
 * Nominatim löst die PLZ in einen Mittelpunkt auf. Die eigentliche Radius-
 * Suche erfolgt anschließend über Overpass und den OSM-Schlüssel
 * `amenity=fuel`. Damit ist die Standortsuche unabhängig von Tankerkönig und
 * liefert echte Radien von 5, 10, 15, 20 oder 25 Kilometern.
 */
class StationLookupService
{
    /** @var array<int, int> */
    private const ALLOWED_RADII_KM = [5, 10, 15, 20, 25];

    /**
     * @return array{
     *     location: array{name: string, latitude: float, longitude: float},
     *     radius_km: int,
     *     stations: array<int, array<string, mixed>>
     * }
     */
    public function searchByPostalCode(string $postalCode, int $radiusKm = 25): array
    {
        $postalCode = trim($postalCode);

        if (! preg_match('/^\d{5}$/', $postalCode)) {
            throw new StationLookupException('Bitte geben Sie eine gültige deutsche Postleitzahl mit fünf Ziffern ein.');
        }

        if (! in_array($radiusKm, self::ALLOWED_RADII_KM, true)) {
            throw new StationLookupException('Bitte wählen Sie einen Suchradius von 5, 10, 15, 20 oder 25 Kilometern.');
        }

        $location = $this->geocodePostalCode($postalCode);

        return [
            'location' => $location,
            'radius_km' => $radiusKm,
            'stations' => $this->loadStations($location['latitude'], $location['longitude'], $radiusKm),
        ];
    }

    /** @return array{name: string, latitude: float, longitude: float} */
    private function geocodePostalCode(string $postalCode): array
    {
        return Cache::remember(
            "station-lookup:geocode:de:{$postalCode}",
            now()->addDays(30),
            function () use ($postalCode): array {
                try {
                    $response = Http::acceptJson()
                        ->withHeaders($this->identificationHeaders())
                        ->timeout(10)
                        ->retry(2, 350)
                        ->get((string) config('services.station_geocoder.url'), [
                            'postalcode' => $postalCode,
                            'countrycodes' => 'de',
                            'country' => 'Deutschland',
                            'format' => 'jsonv2',
                            'addressdetails' => 1,
                            'limit' => 1,
                        ])
                        ->throw();
                } catch (ConnectionException|RequestException $exception) {
                    throw new StationLookupException('Die Postleitzahl konnte derzeit nicht aufgelöst werden. Bitte versuchen Sie es später erneut.', previous: $exception);
                }

                $result = $response->json('0');

                if (! is_array($result) || ! isset($result['lat'], $result['lon'])) {
                    throw new StationLookupException("Zur Postleitzahl {$postalCode} wurde kein Ort gefunden.");
                }

                return [
                    'name' => (string) ($result['display_name'] ?? $postalCode),
                    'latitude' => (float) $result['lat'],
                    'longitude' => (float) $result['lon'],
                ];
            },
        );
    }

    /** @return array<int, array<string, mixed>> */
    private function loadStations(float $latitude, float $longitude, int $radiusKm): array
    {
        $cacheKey = sprintf(
            'station-lookup:osm:%s:%s:%d',
            number_format($latitude, 5, '.', ''),
            number_format($longitude, 5, '.', ''),
            $radiusKm,
        );

        // Tankstellen-Stammdaten ändern sich selten. Der lange Cache schont die
        // gemeinschaftlich betriebenen OSM-Dienste bei wiederholten Suchen.
        return Cache::remember($cacheKey, now()->addDay(), function () use ($latitude, $longitude, $radiusKm): array {
            $radiusMeters = $radiusKm * 1000;
            $query = sprintf(
                '[out:json][timeout:25];nwr["amenity"="fuel"](around:%d,%.7F,%.7F);out center tags;',
                $radiusMeters,
                $latitude,
                $longitude,
            );

            try {
                $response = Http::acceptJson()
                    ->asForm()
                    ->withHeaders($this->identificationHeaders())
                    ->timeout(30)
                    ->retry(2, 500)
                    ->post((string) config('services.overpass.url'), ['data' => $query])
                    ->throw();
            } catch (ConnectionException|RequestException $exception) {
                throw new StationLookupException('Die Tankstellen konnten derzeit nicht geladen werden. Bitte versuchen Sie es später erneut.', previous: $exception);
            }

            if (! is_array($response->json('elements'))) {
                throw new StationLookupException('Die Standort-Schnittstelle hat keine gültige Ergebnisliste geliefert.');
            }

            return collect($response->json('elements'))
                ->filter(fn (mixed $element): bool => is_array($element) && isset($element['id'], $element['type']))
                ->filter(fn (array $element): bool => $this->isLikelyPublicStation($element))
                ->map(fn (array $element): array => $this->normalizeStation($element, $latitude, $longitude))
                ->sortBy('distance_km')
                ->values()
                ->all();
        });
    }

    /**
     * Blendet klar private Betriebstankstellen und offensichtlich unvollständige
     * OSM-Einträge aus. Ein Name mit "Tankstelle", eine Marke, eine Adresse oder
     * hinterlegte Kraftstoffarten reichen als belastbares Stationsmerkmal aus.
     *
     * @param  array<string, mixed>  $element
     */
    private function isLikelyPublicStation(array $element): bool
    {
        $tags = is_array($element['tags'] ?? null) ? $element['tags'] : [];

        if (in_array($tags['access'] ?? null, ['private', 'no'], true)) {
            return false;
        }

        $hasFuelTag = collect(array_keys($tags))->contains(
            fn (string $key): bool => str_starts_with($key, 'fuel:'),
        );
        $name = strtolower((string) ($tags['name'] ?? ''));

        return filled($tags['brand'] ?? null)
            || filled($tags['operator'] ?? null)
            || filled($tags['addr:street'] ?? null)
            || filled($tags['addr:postcode'] ?? null)
            || $hasFuelTag
            || str_contains($name, 'tankstelle')
            || str_contains($name, 'tank station');
    }

    /** @param array<string, mixed> $element */
    private function normalizeStation(array $element, float $searchLatitude, float $searchLongitude): array
    {
        $tags = is_array($element['tags'] ?? null) ? $element['tags'] : [];
        $latitude = $element['lat'] ?? data_get($element, 'center.lat');
        $longitude = $element['lon'] ?? data_get($element, 'center.lon');
        $latitude = is_numeric($latitude) ? (float) $latitude : null;
        $longitude = is_numeric($longitude) ? (float) $longitude : null;
        $brand = trim((string) ($tags['brand'] ?? $tags['operator'] ?? ''));
        $city = trim((string) ($tags['addr:city'] ?? $tags['addr:place'] ?? ''));
        $name = trim((string) ($tags['name'] ?? $tags['operator'] ?? $tags['brand'] ?? 'Tankstelle'));

        // Viele OSM-Einträge tragen als Namen nur die Marke. Für die interne
        // Stationsliste wird daraus eine eindeutigere, weiterhin editierbare
        // Bezeichnung wie "Aral Tankstelle Petersberg".
        if ($brand !== '' && mb_strtolower($name) === mb_strtolower($brand) && $city !== '') {
            $name = "{$brand} Tankstelle {$city}";
        }

        return [
            'source_provider' => 'openstreetmap',
            // Der Objekttyp gehört zur ID, da Knoten und Flächen dieselbe
            // numerische OSM-ID besitzen können.
            'source_station_id' => $element['type'].'/'.$element['id'],
            'name' => $name,
            'brand' => $brand,
            'street' => trim((string) ($tags['addr:street'] ?? '')),
            'house_number' => filled($tags['addr:housenumber'] ?? null) ? (string) $tags['addr:housenumber'] : null,
            'postal_code' => (string) ($tags['addr:postcode'] ?? ''),
            'city' => $city,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'distance_km' => ($latitude === null || $longitude === null)
                ? 0.0
                : round($this->distanceInKilometres($searchLatitude, $searchLongitude, $latitude, $longitude), 1),
            'is_open' => null,
            'fuel_types' => $this->extractFuelTypes($tags),
            'opening_hours_raw' => filled($tags['opening_hours'] ?? null) ? (string) $tags['opening_hours'] : null,
            // OpenStreetMap enthält Standort-Stammdaten, jedoch keine verlässlichen
            // Echtzeitpreise. Diese bleiben bewusst leer und werden nicht erfunden.
            'price_super' => null,
            'price_e10' => null,
            'price_diesel' => null,
            'prices_updated_at' => null,
        ];
    }

    /** @param array<string, mixed> $tags
     * @return array<int, string>
     */
    private function extractFuelTypes(array $tags): array
    {
        return collect([
            ($tags['fuel:e5'] ?? $tags['fuel:octane_95'] ?? null) === 'yes' ? 'Super E5' : null,
            ($tags['fuel:e10'] ?? null) === 'yes' ? 'Super E10' : null,
            ($tags['fuel:diesel'] ?? null) === 'yes' ? 'Diesel' : null,
            ($tags['fuel:lpg'] ?? null) === 'yes' ? 'LPG' : null,
            ($tags['fuel:adblue'] ?? null) === 'yes' ? 'AdBlue' : null,
            ($tags['fuel:h2'] ?? null) === 'yes' ? 'Wasserstoff' : null,
        ])->filter()->values()->all();
    }

    private function distanceInKilometres(float $fromLatitude, float $fromLongitude, float $toLatitude, float $toLongitude): float
    {
        $earthRadiusKm = 6371;
        $latitudeDifference = deg2rad($toLatitude - $fromLatitude);
        $longitudeDifference = deg2rad($toLongitude - $fromLongitude);
        $a = sin($latitudeDifference / 2) ** 2
            + cos(deg2rad($fromLatitude))
            * cos(deg2rad($toLatitude))
            * sin($longitudeDifference / 2) ** 2;

        return $earthRadiusKm * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    /** @return array<string, string> */
    private function identificationHeaders(): array
    {
        return [
            'User-Agent' => (string) config('services.openstreetmap.user_agent'),
            'Referer' => (string) config('app.url'),
            'Accept-Language' => 'de',
        ];
    }
}
