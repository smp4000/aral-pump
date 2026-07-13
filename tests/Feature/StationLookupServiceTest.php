<?php

namespace Tests\Feature;

use App\Exceptions\StationLookupException;
use App\Services\StationLookupService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Prüft PLZ-Auflösung, echten Radius und Normalisierung der OSM-Daten,
 * ohne in der Testsuite Anfragen an gemeinschaftliche Server zu senden.
 */
class StationLookupServiceTest extends TestCase
{
    public function test_it_finds_and_normalizes_osm_stations_within_25_kilometres(): void
    {
        config([
            'services.station_geocoder.url' => 'https://geocoder.test/search',
            'services.overpass.url' => 'https://overpass.test/interpreter',
            'services.openstreetmap.user_agent' => 'StationDesk-Test',
        ]);

        Http::fake([
            'https://geocoder.test/*' => Http::response([[
                'display_name' => '36100 Petersberg, Hessen, Deutschland',
                'lat' => '50.55923',
                'lon' => '9.71252',
            ]]),
            'https://overpass.test/*' => Http::response([
                'elements' => [
                    [
                        'type' => 'node',
                        'id' => 123456,
                        'lat' => 50.5512,
                        'lon' => 9.6751,
                        'tags' => [
                            'amenity' => 'fuel',
                            'name' => 'ARAL',
                            'brand' => 'ARAL',
                            'addr:street' => 'Leipziger Straße',
                            'addr:housenumber' => '12a',
                            'addr:postcode' => '36037',
                            'addr:city' => 'Fulda',
                            'fuel:e10' => 'yes',
                            'fuel:diesel' => 'yes',
                            'opening_hours' => 'Mo-Su 06:00-22:00',
                        ],
                    ],
                    [
                        'type' => 'node',
                        'id' => 999999,
                        'lat' => 50.5520,
                        'lon' => 9.6760,
                        'tags' => ['amenity' => 'fuel', 'name' => 'Freiwillige Feuerwehr'],
                    ],
                ],
            ]),
        ]);

        $result = app(StationLookupService::class)->searchByPostalCode('36100', 25);
        $station = $result['stations'][0];

        $this->assertSame(25, $result['radius_km']);
        $this->assertCount(1, $result['stations']);
        $this->assertSame('openstreetmap', $station['source_provider']);
        $this->assertSame('node/123456', $station['source_station_id']);
        $this->assertSame('ARAL Tankstelle Fulda', $station['name']);
        $this->assertSame('Leipziger Straße', $station['street']);
        $this->assertSame('12a', $station['house_number']);
        $this->assertContains('Super E10', $station['fuel_types']);
        $this->assertContains('Diesel', $station['fuel_types']);
        $this->assertSame('Mo-Su 06:00-22:00', $station['opening_hours_raw']);
        $this->assertNull($station['price_e10']);

        Http::assertSent(fn (Request $request): bool => $request->url() === 'https://overpass.test/interpreter'
            && str_contains((string) $request['data'], 'around:25000')
            && str_contains((string) $request['data'], 'amenity')
        );
    }

    public function test_it_only_accepts_the_documented_radius_buttons(): void
    {
        Http::preventStrayRequests();

        $this->expectException(StationLookupException::class);
        $this->expectExceptionMessage('5, 10, 15, 20 oder 25');

        app(StationLookupService::class)->searchByPostalCode('36100', 12);
    }
}
