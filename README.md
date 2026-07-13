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
- geführter Anlage-Wizard: PLZ-Suche mit Exakt/3/5/10/20-km-Auswahl
- offene und aktuell geschlossene Tankstellen in derselben Ergebnisliste
- automatische Übernahme von Marke, Adresse, GPS, Preisen, Kraftstoffarten und Öffnungszeiten
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

Der Anlage-Wizard nutzt Benzinpreis-Aktuell.de als alleinige Quelle für alle
fachlichen Tankstellen- und Preisdaten. Nominatim wird nur verwendet, um aus
der eingegebenen PLZ den für die Quell-URL erforderlichen Ortsnamen zu bilden.
Die Ergebnisliste enthält auch die von der Quelle separat aufgeführten aktuell
geschlossenen Stationen bzw. Stationen ohne gemeldeten Dieselpreis.

```dotenv
STATION_GEOCODER_URL=https://nominatim.openstreetmap.org/search
BENZINPREIS_AKTUELL_URL=https://www.benzinpreis-aktuell.de
BENZINPREIS_AKTUELL_USER_AGENT="StationDesk/1.0 (+https://ihre-domain.de; kontakt@ihre-domain.de)"
```

Nach einer Konfigurationsänderung muss `php artisan config:clear` ausgeführt
werden. Listen werden 15 Minuten und Detailseiten 30 Minuten zwischengespeichert.
Die sichtbare Quellenangabe nennt Benzinpreis-Aktuell.de; die externe Stations-ID
verhindert Dubletten und ermöglicht spätere Aktualisierungen.

## Zugänge

- Plattform-Administration: `/admin`
- Landingpage-Pflege: `/admin/landing-page-settings`
- Partnerbereich: `/partner`
- Partnerregistrierung: `/partner/register`
- Partner-Login: `/partner/login`

Der erste Plattform-Administrator wird beim Seeding aus diesen Variablen erzeugt:

```dotenv
PLATFORM_ADMIN_NAME="Plattform Administrator"
PLATFORM_ADMIN_EMAIL=admin@stationdesk.local
PLATFORM_ADMIN_PASSWORD=password
```

Das Standardpasswort ist ausschließlich für die lokale Entwicklung gedacht und
muss vor einem erreichbaren Test- oder Produktivbetrieb geändert werden.

Für lokale Oberflächentests kann der Seeder zusätzlich einen Demo-Partner
anlegen. Dieser Zugang ist in Produktion zwingend zu deaktivieren:

```dotenv
DEMO_PARTNER_ENABLED=true
DEMO_PARTNER_EMAIL=partner@stationdesk.local
DEMO_PARTNER_PASSWORD=password
```

## Qualitätssicherung

```bash
vendor/bin/pint --test
php artisan test
```

Die Tests prüfen unter anderem Registrierung, Testzeitraum, Panel-Zugriff und
die Trennung von Tankstellen verschiedener Partner.
