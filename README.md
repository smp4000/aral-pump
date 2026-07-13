# Station Desk

Station Desk ist eine mandantenfähige Arbeitsplattform für Tankstellenpartner,
Stationsleiter und Mitarbeiter. Das Projekt basiert auf Laravel 12, Filament 5
und einer MySQL-kompatiblen Datenbank.

## Aktueller Funktionsumfang

- moderne öffentliche Landingpage
- Landingpage-Inhalte vollständig über den Plattform-Adminbereich pflegbar
- Aral-blaues responsives Design mit Hero, Funktionen, Ablauf, Datenschutz,
  Trial-Preisbox, Abschlussbereich und Footer
- Selbstregistrierung neuer Tankstellenpartner
- automatische 30-tägige Testphase
- getrennte Filament-Bereiche für Plattform-Administration und Partner
- zentrale Verwaltung aller Partner, Benutzer und Tankstellen
- Partnerverwaltung der ausschließlich eigenen Tankstellen
- geführter Anlage-Wizard: PLZ-Suche mit 5/10/15/20/25-km-Auswahl
- automatische Übernahme von Marke, Adresse, GPS-Position und Kraftstoffarten
- vollständige Tankstellen-Stammdaten in gegliederten Formular-Tabs
- zentral gepflegte Markenliste mit 30 Marken, Reihenfolge, Farben und Logos
- öffentliche Stations-UUID, Soft Deletes und unveränderliches Audit-Log
- GPS-Entfernungsberechnung sowie verschlüsselte MDE- und Drucker-Setup-Tokens
- verschlüsselte, maskiert angezeigte Bankkonten pro Tankstelle
- Zuordnung von Mitarbeitern zu mehreren Tankstellen mit Stationsrolle
- serverseitig abgesicherte Mandantentrennung
- Deaktivierung abgelaufener Konten ohne automatische Datenlöschung

## Lokale Einrichtung

```bash
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

Die Standardkonfiguration erwartet die lokale Datenbank `aral_pump` auf Port
3306. Zugangsdaten können in der lokalen `.env` angepasst werden.

### Tankstellensuche einrichten

Der Anlage-Wizard nutzt OpenStreetMap-Standortdaten. Nominatim löst die
eingegebene Postleitzahl auf, Overpass sucht Tankstellen im gewählten Radius.
Es ist kein Tankerkönig-Schlüssel erforderlich. Echtzeitpreise gehören bewusst
nicht zu dieser Standortquelle und bleiben bei der Anlage leer.

```dotenv
STATION_GEOCODER_URL=https://nominatim.openstreetmap.org/search
OVERPASS_API_URL=https://overpass-api.de/api/interpreter
OPENSTREETMAP_USER_AGENT="StationDesk/1.0 (+https://ihre-domain.de; kontakt@ihre-domain.de)"
```

Nach einer Konfigurationsänderung muss `php artisan config:clear` ausgeführt
werden. Suchergebnisse werden zwischengespeichert, die OSM-Attribution wird im
Wizard angezeigt und die externe Stations-ID verhindert Dubletten. Für einen
größeren Produktivbetrieb sollten eigene oder kommerzielle Geocoding- und
Overpass-Instanzen konfiguriert werden.

## Zugänge

- Plattform-Administration: `/admin`
- Landingpage-Pflege: `/admin/landing-page-settings`
- Partnerbereich: `/partner`
- Partnerregistrierung: `/partner/register`

Der erste Plattform-Administrator wird beim Seeding aus diesen Variablen erzeugt:

```dotenv
PLATFORM_ADMIN_NAME="Plattform Administrator"
PLATFORM_ADMIN_EMAIL=admin@stationdesk.local
PLATFORM_ADMIN_PASSWORD=password
```

Das Standardpasswort ist ausschließlich für die lokale Entwicklung gedacht und
muss vor einem erreichbaren Test- oder Produktivbetrieb geändert werden.

## Qualitätssicherung

```bash
vendor/bin/pint --test
php artisan test
```

Die Tests prüfen unter anderem Registrierung, Testzeitraum, Panel-Zugriff und
die Trennung von Tankstellen verschiedener Partner.
