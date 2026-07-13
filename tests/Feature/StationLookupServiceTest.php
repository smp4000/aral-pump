<?php

namespace Tests\Feature;

use App\Exceptions\StationLookupException;
use App\Services\StationLookupService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Prüft offene und geschlossene Treffer sowie die vollständige Übernahme
 * der Detaildaten von Benzinpreis-Aktuell.de, ohne echte Fremdaufrufe im Test.
 */
class StationLookupServiceTest extends TestCase
{
    public function test_it_keeps_closed_stations_and_loads_all_detail_data(): void
    {
        config([
            'services.station_geocoder.url' => 'https://geocoder.test/search',
            'services.benzinpreis_aktuell.url' => 'https://benzin.test',
            'services.benzinpreis_aktuell.user_agent' => 'StationDesk-Test',
        ]);

        $searchHtml = <<<'HTML'
            <html><head><title>Dieselpreise 36100 Petersberg</title></head><body>
            <div id="station-tabc12-petersberg-aral" class="ns_newsquare ns_count_0" data-mid="mts-open">
                <div><em title="Dieselpreis">1.72</em><sup>9</sup></div>
                <p><strong class="isstrong">ARAL Tankstelle Petersberg</strong><br>Petersberger Straße 101<span class="offenbis">22 Uhr</span></p>
                <a href="preise-tabc12-petersberg-aral">mehr infos</a>
            </div>
            <div id="station-tdef34-fulda-esso" class="ns_newsquare ns_count_10000" data-mid="mts-closed">
                <div><em title="Dieselpreis">-,--</em></div>
                <p><strong class="isstrong">ESSO Tankstelle Fulda</strong><br>Beispielweg 5</p>
                <a href="preise-tdef34-fulda-esso">mehr infos</a>
            </div>
            <section><ul><li>Stand: 13.07.2026, 21:59 Uhr</li></ul></section>
            <h3 id="umkreis">Umkreis</h3>
            </body></html>
            HTML;

        $detailHtml = <<<'HTML'
            <html><head>
            <meta property="place:location:latitude" content="50.5592300">
            <meta property="place:location:longitude" content="9.7125200">
            </head><body>
            <div class="preis25 preis_benzin"><em>1,79<sup>9</sup></em></div>
            <div class="preis25 preis_e10"><em>1,73<sup>9</sup></em></div>
            <div class="preis25 preis_diesel"><em>1,72<sup>9</sup></em></div>
            <h2>Wo finde ich die Tankstelle?</h2>
            <p class="centerit">Petersberger Straße 101<br>36100 Petersberg<a href="maps">Navigation</a></p>
            <section id="oeffnungszeiten" data-mtsk="mts-open">
                <p class="e-otimes"><em>06:00 bis 22:00</em><span>Montag</span></p>
                <p class="e-otimes"><em>24 Stunden</em><span>Dienstag</span></p>
                <p class="e-otimes"><em>Geschlossen</em><span>Sonntag</span></p>
            </section>
            </body></html>
            HTML;

        Http::fake([
            'https://geocoder.test/*' => Http::response([[
                'address' => ['village' => 'Petersberg', 'county' => 'Landkreis Fulda'],
            ]]),
            'https://benzin.test/36100-petersberg-aktuelle-dieselpreise*' => Http::response($searchHtml),
            'https://benzin.test/preise-tabc12-petersberg-aral' => Http::response($detailHtml),
        ]);

        $result = app(StationLookupService::class)->searchByPostalCode('36100', 20);

        $this->assertSame(20, $result['radius_km']);
        $this->assertCount(2, $result['stations']);
        $this->assertTrue($result['stations'][0]['is_open']);
        $this->assertFalse($result['stations'][1]['is_open']);
        $this->assertNull($result['stations'][1]['price_diesel']);

        $station = app(StationLookupService::class)->loadStationDetails($result['stations'][0]);

        $this->assertSame('benzinpreis-aktuell', $station['source_provider']);
        $this->assertSame('Petersberger Straße', $station['street']);
        $this->assertSame('101', $station['house_number']);
        $this->assertSame('36100', $station['postal_code']);
        $this->assertSame('Petersberg', $station['city']);
        $this->assertSame(1.799, $station['price_super']);
        $this->assertSame(1.739, $station['price_e10']);
        $this->assertSame(1.729, $station['price_diesel']);
        $this->assertCount(3, $station['opening_hours']);

        Http::assertSent(fn (Request $request): bool => str_contains($request->url(), 'aktuelle-dieselpreise?umkreis=20')
        );
    }

    public function test_it_only_accepts_source_supported_radius_buttons(): void
    {
        Http::preventStrayRequests();

        $this->expectException(StationLookupException::class);
        $this->expectExceptionMessage('Exakt');

        app(StationLookupService::class)->searchByPostalCode('36100', 15);
    }
}
