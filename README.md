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
