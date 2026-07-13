<?php

namespace App\Services;

use App\Exceptions\StationLookupException;
use Carbon\CarbonImmutable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Liest Tankstellen- und Preisdaten von Benzinpreis-Aktuell.de ein.
 *
 * Die PLZ wird ausschließlich zur Bildung der dort verwendeten Orts-URL per
 * Nominatim aufgelöst. Sämtliche fachlichen Tankstellendaten stammen danach
 * von Benzinpreis-Aktuell.de: auch die am Seitenende aufgeführten geschlossenen
 * Stationen bzw. Stationen ohne Dieselpreis werden bewusst nicht ausgefiltert.
 */
class StationLookupService
{
    /** Die Quellseite bietet genau diese Umkreiswerte an. */
    private const ALLOWED_RADII_KM = [0, 3, 5, 10, 20];

    /**
     * @return array{
     *     location: array{name: string, city: string},
     *     radius_km: int,
     *     stations: array<int, array<string, mixed>>
     * }
     */
    public function searchByPostalCode(string $postalCode, int $radiusKm = 5): array
    {
        $postalCode = trim($postalCode);

        if (! preg_match('/^\d{5}$/', $postalCode)) {
            throw new StationLookupException('Bitte geben Sie eine gültige deutsche Postleitzahl mit fünf Ziffern ein.');
        }

        if (! in_array($radiusKm, self::ALLOWED_RADII_KM, true)) {
            throw new StationLookupException('Bitte wählen Sie Exakt oder einen Suchradius von 3, 5, 10 oder 20 Kilometern.');
        }

        $places = $this->resolvePlaces($postalCode);

        foreach ($places as $city) {
            $stations = $this->loadSearchPage($postalCode, $city, $radiusKm);

            if ($stations !== null) {
                return [
                    'location' => ['name' => "{$postalCode} {$city}", 'city' => $city],
                    'radius_km' => $radiusKm,
                    'stations' => $stations,
                ];
            }
        }

        throw new StationLookupException("Für die Postleitzahl {$postalCode} wurde bei Benzinpreis-Aktuell.de keine Ergebnisliste gefunden.");
    }

    /**
     * Lädt nach der Auswahl die Detailseite und ergänzt Adresse, Koordinaten,
     * alle drei Preise und die vollständigen Wochenöffnungszeiten.
     *
     * @param  array<string, mixed>  $station
     * @return array<string, mixed>
     */
    public function loadStationDetails(array $station): array
    {
        $detailSlug = (string) ($station['source_detail_slug'] ?? '');

        if (! preg_match('/^[a-z0-9-]+$/', $detailSlug)) {
            throw new StationLookupException('Die Detailadresse der ausgewählten Tankstelle ist ungültig.');
        }

        $details = Cache::remember(
            'station-lookup:benzinpreis-details:'.sha1($detailSlug),
            now()->addMinutes(30),
            function () use ($detailSlug): array {
                $url = $this->baseUrl().'/'.$detailSlug;
                $html = $this->fetchHtml($url, 'Die Tankstellendetails konnten derzeit nicht geladen werden.');

                return $this->parseDetailPage($html, $url);
            },
        );

        // Der Listenstatus bleibt maßgeblich: Detailseiten zeigen teilweise
        // noch den letzten bekannten Preis einer aktuell geschlossenen Station.
        return array_replace($station, $details, [
            'is_open' => $station['is_open'] ?? null,
            'source_provider' => 'benzinpreis-aktuell',
            'source_station_id' => $station['source_station_id'],
            'source_detail_slug' => $detailSlug,
        ]);
    }

    /** @return array<int, string> */
    private function resolvePlaces(string $postalCode): array
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
                            'format' => 'jsonv2',
                            'addressdetails' => 1,
                            'limit' => 1,
                        ])
                        ->throw();
                } catch (ConnectionException|RequestException $exception) {
                    throw new StationLookupException('Die Postleitzahl konnte derzeit nicht aufgelöst werden.', previous: $exception);
                }

                $result = $response->json('0');

                if (! is_array($result)) {
                    throw new StationLookupException("Zur Postleitzahl {$postalCode} wurde kein Ort gefunden.");
                }

                $address = is_array($result['address'] ?? null) ? $result['address'] : [];
                $primary = $address['city'] ?? $address['town'] ?? $address['village'] ?? $address['municipality'] ?? null;
                $places = collect([
                    $primary,
                    $address['municipality'] ?? null,
                    $this->countyName((string) ($address['county'] ?? '')),
                ])->filter()->map(fn (string $place): string => trim($place))->unique()->values()->all();

                if ($places === []) {
                    throw new StationLookupException("Zur Postleitzahl {$postalCode} wurde kein Ortsname gefunden.");
                }

                return $places;
            },
        );
    }

    /** @return array<int, array<string, mixed>>|null */
    private function loadSearchPage(string $postalCode, string $city, int $radiusKm): ?array
    {
        $citySlug = $this->cityToSlug($city);
        $cacheKey = "station-lookup:benzinpreis-list:{$postalCode}:{$citySlug}:{$radiusKm}";

        return Cache::remember($cacheKey, now()->addMinutes(15), function () use ($postalCode, $citySlug, $radiusKm): ?array {
            $path = "{$postalCode}-{$citySlug}-aktuelle-dieselpreise";
            $url = $this->baseUrl().'/'.$path;

            // Fünf Kilometer sind die Standardansicht der Quelle. Alle anderen
            // angebotenen Werte werden als expliziter Umkreisparameter angehängt.
            if ($radiusKm !== 5) {
                $url .= '?'.http_build_query(['umkreis' => $radiusKm]);
            }

            try {
                $html = $this->fetchHtml($url, 'Die Tankstellenliste konnte derzeit nicht geladen werden.');
            } catch (StationLookupException $exception) {
                if ($exception->getCode() === 404) {
                    return null;
                }

                throw $exception;
            }

            if (preg_match('/<title>[^<]*(?:nicht gefunden|404|401)/i', $html)) {
                return null;
            }

            $stations = $this->parseSearchPage($html);

            return $stations === [] ? null : $stations;
        });
    }

    /** @return array<int, array<string, mixed>> */
    private function parseSearchPage(string $html): array
    {
        preg_match('/Stand:\s*(\d{2}\.\d{2}\.\d{4}),\s*(\d{2}:\d{2})\s*Uhr/i', $html, $updatedMatch);
        $updatedAt = null;

        if (isset($updatedMatch[1], $updatedMatch[2])) {
            $updatedAt = CarbonImmutable::createFromFormat(
                'd.m.Y H:i',
                $updatedMatch[1].' '.$updatedMatch[2],
                'Europe/Berlin',
            )?->toIso8601String();
        }

        if (! preg_match_all(
            '/<div id="station-t([a-f0-9]+)-([^"]+)"([^>]*)>(.+?)(?=<div id="station-t|<h3 id="umkreis"|$)/si',
            $html,
            $blocks,
            PREG_SET_ORDER,
        )) {
            return [];
        }

        return collect($blocks)
            ->map(function (array $block) use ($updatedAt): ?array {
                $hash = $block[1];
                $slug = rtrim($block[2], '/');
                $attributes = $block[3];
                $blockHtml = $block[4];

                if (! preg_match('/<strong class="isstrong">([^<]+)<\/strong><br>\s*([^<]+)/si', $blockHtml, $nameMatch)) {
                    return null;
                }

                $name = $this->cleanText($nameMatch[1]);
                $streetLine = $this->cleanText($nameMatch[2]);
                preg_match('/data-mid="([^"]+)"/i', $attributes, $mtsMatch);
                preg_match('/href="(preise-t[^"]+)"/i', $blockHtml, $detailMatch);
                preg_match('/<em[^>]*>([\d.,-]+)<\/em>\s*<sup>(\d)<\/sup>/i', $blockHtml, $priceMatch);

                $dieselPrice = null;
                if (isset($priceMatch[1], $priceMatch[2]) && preg_match('/\d/', $priceMatch[1])) {
                    $dieselPrice = (float) (str_replace(',', '.', $priceMatch[1]).$priceMatch[2]);
                }

                return [
                    'source_provider' => 'benzinpreis-aktuell',
                    'source_station_id' => $hash,
                    'source_detail_slug' => $detailMatch[1] ?? "preise-t{$hash}-{$slug}",
                    'source_mts_id' => $mtsMatch[1] ?? null,
                    'name' => $name,
                    'brand' => $this->inferBrand($name),
                    'street' => $streetLine,
                    'house_number' => null,
                    'postal_code' => '',
                    'city' => '',
                    'latitude' => null,
                    'longitude' => null,
                    'distance_km' => null,
                    'is_open' => $dieselPrice !== null,
                    'fuel_types' => $dieselPrice !== null ? ['Diesel'] : [],
                    'opening_hours' => [],
                    'price_super' => null,
                    'price_e10' => null,
                    'price_diesel' => $dieselPrice,
                    'prices_updated_at' => $updatedAt,
                ];
            })
            ->filter()
            ->unique('source_station_id')
            ->values()
            ->all();
    }

    /** @return array<string, mixed> */
    private function parseDetailPage(string $html, string $url): array
    {
        $latitude = $this->firstMatch('/property="place:location:latitude"\s+content="([\d.-]+)"/i', $html);
        $longitude = $this->firstMatch('/property="place:location:longitude"\s+content="([\d.-]+)"/i', $html);
        [$street, $houseNumber, $postalCode, $city] = $this->parseAddress($html);
        $prices = $this->parsePrices($html);
        $openingHours = $this->parseOpeningHours($html);

        return [
            'street' => $street,
            'house_number' => $houseNumber,
            'postal_code' => $postalCode,
            'city' => $city,
            'latitude' => is_numeric($latitude) ? (float) $latitude : null,
            'longitude' => is_numeric($longitude) ? (float) $longitude : null,
            'opening_hours' => $openingHours,
            'price_super' => $prices['super'],
            'price_e10' => $prices['e10'],
            'price_diesel' => $prices['diesel'],
            'fuel_types' => collect([
                $prices['super'] !== null ? 'Super E5' : null,
                $prices['e10'] !== null ? 'Super E10' : null,
                $prices['diesel'] !== null ? 'Diesel' : null,
            ])->filter()->values()->all(),
            'source_url' => $url,
        ];
    }

    /** @return array{0: string, 1: ?string, 2: string, 3: string} */
    private function parseAddress(string $html): array
    {
        if (! preg_match('/Wo finde ich die Tankstelle\?\s*<\/h2>\s*<p[^>]*>\s*(.*?)\s*<br>\s*(\d{5})\s+([^<]+)/si', $html, $match)) {
            return ['', null, '', ''];
        }

        $streetLine = $this->cleanText($match[1]);
        $postalCode = trim($match[2]);
        $city = $this->cleanText($match[3]);

        if (preg_match('/^(.*\D)\s+(\d+[a-zA-Z]?(?:[-\/]\d+[a-zA-Z]?)?)$/u', $streetLine, $streetMatch)) {
            return [trim($streetMatch[1]), trim($streetMatch[2]), $postalCode, $city];
        }

        return [$streetLine, null, $postalCode, $city];
    }

    /** @return array{super: ?float, e10: ?float, diesel: ?float} */
    private function parsePrices(string $html): array
    {
        $prices = ['super' => null, 'e10' => null, 'diesel' => null];

        if (preg_match_all('/<div[^>]*class="[^"]*preis25[^\"]*preis_(benzin|e10|diesel)[^"]*"[^>]*>\s*<em>([\d,.]+)<sup>(\d)<\/sup>/si', $html, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $key = $match[1] === 'benzin' ? 'super' : $match[1];
                $prices[$key] = (float) (str_replace(',', '.', $match[2]).$match[3]);
            }
        }

        return $prices;
    }

    /** @return array<int, array{day: string, open: ?string, close: ?string, closed: bool}> */
    private function parseOpeningHours(string $html): array
    {
        $days = [
            'Montag' => 'monday', 'Dienstag' => 'tuesday', 'Mittwoch' => 'wednesday',
            'Donnerstag' => 'thursday', 'Freitag' => 'friday', 'Samstag' => 'saturday',
            'Sonntag' => 'sunday',
        ];

        if (! preg_match_all('/<p\s+class="e-otimes[^"]*">\s*<em>([^<]+)<\/em>\s*<span>([^<]+)<\/span>/i', $html, $matches, PREG_SET_ORDER)) {
            return [];
        }

        return collect($matches)
            ->map(function (array $match) use ($days): ?array {
                $day = $days[$this->cleanText($match[2])] ?? null;
                $time = $this->cleanText($match[1]);

                if ($day === null) {
                    return null;
                }

                if (str_contains(mb_strtolower($time), '24 stunden')) {
                    return ['day' => $day, 'open' => '00:00', 'close' => '00:00', 'closed' => false];
                }

                if (preg_match('/(\d{2}:\d{2})\s*(?:bis|-)\s*(\d{2}:\d{2})/i', $time, $timeMatch)) {
                    return ['day' => $day, 'open' => $timeMatch[1], 'close' => $timeMatch[2], 'closed' => false];
                }

                return [
                    'day' => $day,
                    'open' => null,
                    'close' => null,
                    'closed' => str_contains(mb_strtolower($time), 'geschlossen'),
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    private function fetchHtml(string $url, string $errorMessage): string
    {
        try {
            $response = Http::withHeaders($this->identificationHeaders())
                ->timeout(15)
                ->retry(2, 400)
                ->get($url);
        } catch (ConnectionException $exception) {
            throw new StationLookupException($errorMessage, previous: $exception);
        }

        if ($response->status() === 404) {
            throw new StationLookupException($errorMessage, 404);
        }

        try {
            return $response->throw()->body();
        } catch (RequestException $exception) {
            throw new StationLookupException($errorMessage, previous: $exception);
        }
    }

    private function inferBrand(string $name): string
    {
        $knownBrands = [
            'TotalEnergies', 'Raiffeisen', 'Westfalen', 'Nordoel', 'Calpam',
            'ARAL', 'Shell', 'ESSO', 'JET', 'AVIA', 'OIL!', 'HEM', 'bft',
            'Q1', 'OMV', 'ENI', 'Agip', 'Globus', 'Sprint', 'team', 'Gulf',
        ];

        foreach ($knownBrands as $brand) {
            if (str_starts_with(mb_strtolower($name), mb_strtolower($brand))) {
                return $brand;
            }
        }

        return '';
    }

    private function cityToSlug(string $city): string
    {
        return Str::slug(str_replace(['ä', 'ö', 'ü', 'ß'], ['ae', 'oe', 'ue', 'ss'], mb_strtolower($city)));
    }

    private function countyName(string $county): ?string
    {
        return preg_match('/^(?:Landkreis|Kreis)\s+(.+)$/i', $county, $match)
            ? trim($match[1])
            : null;
    }

    private function cleanText(string $value): string
    {
        return trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8')) ?? '');
    }

    private function firstMatch(string $pattern, string $subject): ?string
    {
        return preg_match($pattern, $subject, $match) ? $match[1] : null;
    }

    private function baseUrl(): string
    {
        return rtrim((string) config('services.benzinpreis_aktuell.url'), '/');
    }

    /** @return array<string, string> */
    private function identificationHeaders(): array
    {
        return [
            'User-Agent' => (string) config('services.benzinpreis_aktuell.user_agent'),
            'Accept-Language' => 'de-DE,de;q=0.9',
        ];
    }
}
