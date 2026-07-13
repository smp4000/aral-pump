<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Redaktionelle Inhalte der öffentlichen Station-Desk-Landingpage.
 *
 * Jeder Hauptbereich wird als strukturierte JSON-Gruppe gespeichert. So kann
 * der Plattform-Administrator Texte, Karten, Listen und Kennzahlen vollständig
 * über Filament pflegen, ohne für jede Inhaltsänderung Code anzupassen.
 */
class LandingPageSetting extends Model
{
    protected $fillable = [
        'general', 'hero', 'features', 'steps', 'privacy',
        'pricing', 'cta', 'footer', 'is_published',
    ];

    protected function casts(): array
    {
        return [
            'general' => 'array',
            'hero' => 'array',
            'features' => 'array',
            'steps' => 'array',
            'privacy' => 'array',
            'pricing' => 'array',
            'cta' => 'array',
            'footer' => 'array',
            'is_published' => 'boolean',
        ];
    }

    /**
     * Liefert die deutschen Startinhalte für neue Installationen und Tests.
     * Die Werte sind danach vollständig im Plattform-Adminbereich veränderbar.
     *
     * @return array<string, mixed>
     */
    public static function defaultContent(): array
    {
        return [
            'general' => [
                'site_name' => 'Station Desk',
                'logo_path' => null,
                'seo_title' => 'Station Desk – Ihre Tankstellen. Digital verwaltet.',
                'seo_description' => 'Die moderne Arbeitsplattform für Tankstellenpartner, Stationsleiter und Mitarbeiter.',
                'nav_features' => 'Funktionen',
                'nav_process' => 'So funktioniert’s',
                'nav_pricing' => 'Preise',
                'login_label' => 'Anmelden',
                'register_label' => 'Kostenlos testen',
            ],
            'hero' => [
                'badge' => '30 Tage kostenlos testen',
                'title_before' => 'Ihre Tankstellen.',
                'highlight' => 'Digital verwaltet.',
                'description' => 'Die moderne Arbeitsplattform für Tankstellenpartner. Mitarbeiter, Aufgaben, Dokumente und Arbeitsabläufe – alles an einem Ort.',
                'primary_label' => 'Jetzt kostenlos starten',
                'secondary_label' => 'Mehr erfahren',
                'trust_items' => ['Keine Kreditkarte nötig', 'DSGVO-konform geplant', 'Made in Germany'],
                'stats' => [
                    ['label' => 'Tankstellen', 'value' => '2', 'note' => '+2 diesen Monat', 'tone' => 'blue'],
                    ['label' => 'Mitarbeiter', 'value' => '48', 'note' => 'Alle aktiv', 'tone' => 'green'],
                    ['label' => 'Aufgaben', 'value' => '156', 'note' => '+8 diese Woche', 'tone' => 'yellow'],
                    ['label' => 'Dokumente', 'value' => '234', 'note' => '12 zur Unterschrift', 'tone' => 'purple'],
                ],
            ],
            'features' => [
                'kicker' => 'Alles, was Sie brauchen',
                'title' => 'Eine Plattform. Alle Funktionen.',
                'description' => 'Von der Mitarbeiterverwaltung bis zur Tagesaufgabe – Station Desk deckt alle Bereiche Ihres Tankstellenbetriebs ab.',
                'items' => [
                    ['icon' => 'building', 'title' => 'Tankstellen-Verwaltung', 'description' => 'Alle Standorte auf einen Blick. Stammdaten, Öffnungszeiten und Marken zentral verwalten.', 'tone' => 'blue'],
                    ['icon' => 'users', 'title' => 'Mitarbeiter & Personal', 'description' => 'Mitarbeiter-Zuordnung, Einladungen und Rollen mit individuellen Berechtigungen.', 'tone' => 'green'],
                    ['icon' => 'document', 'title' => 'Dokumenten-Management', 'description' => 'Arbeitsverträge, Nachweise und Dokumente digital und sicher verwalten.', 'tone' => 'purple'],
                    ['icon' => 'contacts', 'title' => 'Tagesaufgaben', 'description' => 'Wiederkehrende Aufgaben anlegen, zuweisen und transparent nachverfolgen.', 'tone' => 'yellow'],
                    ['icon' => 'calendar', 'title' => 'MHD & Abschriften', 'description' => 'Mindesthaltbarkeit erfassen und Abschriften direkt am MDE dokumentieren.', 'tone' => 'pink'],
                    ['icon' => 'sparkles', 'title' => 'MDE-Unterstützung', 'description' => 'QR-Code, GPS, Barcode und NFC verbinden Mitarbeiter sicher mit der Tankstelle.', 'tone' => 'indigo'],
                ],
            ],
            'steps' => [
                'kicker' => 'Einfach starten',
                'title' => 'In 3 Schritten startklar',
                'items' => [
                    ['number' => '1', 'title' => 'Registrieren', 'description' => 'Erstellen Sie Ihr Konto in wenigen Minuten und testen Sie 30 Tage kostenlos.'],
                    ['number' => '2', 'title' => 'Einrichten', 'description' => 'Legen Sie Tankstellen und Mitarbeiter an und vergeben Sie Rollen.'],
                    ['number' => '3', 'title' => 'Loslegen', 'description' => 'Verwalten Sie Teams, Aufgaben und Abläufe übersichtlich an einem Ort.'],
                ],
            ],
            'privacy' => [
                'kicker' => 'Datenschutz',
                'title' => 'DSGVO-konform. Von Anfang an.',
                'description' => 'Ihre Daten und die Ihrer Mitarbeiter sind uns wichtig. Station Desk wird nach den strengen Anforderungen der DSGVO entwickelt.',
                'points' => [
                    ['title' => 'AES-256 Verschlüsselung', 'description' => 'Sensible Daten werden verschlüsselt gespeichert und übertragen.'],
                    ['title' => 'Lückenloses Audit-Log', 'description' => 'Relevante Veränderungen werden nachvollziehbar protokolliert.'],
                    ['title' => 'Betroffenenrechte', 'description' => 'Datenexport, Berichtigung und Löschung werden technisch unterstützt.'],
                    ['title' => 'Mandantentrennung', 'description' => 'Vollständige Datentrennung zwischen Partnern und Tankstellen.'],
                ],
                'status_items' => [
                    ['label' => 'Verschlüsselung', 'status' => 'Aktiv'],
                    ['label' => 'Audit-Logging', 'status' => 'Aktiv'],
                    ['label' => 'Einwilligungen', 'status' => 'Vollständig'],
                    ['label' => 'Daten-Aufbewahrung', 'status' => 'Konform'],
                ],
            ],
            'pricing' => [
                'kicker' => 'Faire Preise',
                'title' => 'Starten Sie mit dem kostenlosen Trial',
                'description' => '30 Tage voller Zugang. Keine Kreditkarte. Keine versteckten Kosten.',
                'badge' => 'Jetzt starten',
                'plan_name' => '30-Tage-Trial',
                'price' => '0 €',
                'price_note' => 'Voller Zugang, keine Einrichtungsgebühr.',
                'features' => ['Alle Grundfunktionen freigeschaltet', 'Unbegrenzt Tankstellen & Mitarbeiter', 'MDE-Vorbereitung inklusive', 'DSGVO-konforme Datentrennung', 'Kein automatisches Abo'],
                'button_label' => 'Kostenlos registrieren',
            ],
            'cta' => [
                'title' => 'Bereit, Ihre Tankstellen digital zu verwalten?',
                'description' => 'Starten Sie heute mit Station Desk und erleben Sie, wie einfach Tankstellen-Management sein kann.',
                'primary_label' => '30 Tage kostenlos testen',
                'secondary_label' => 'Ich habe bereits ein Konto',
            ],
            'footer' => [
                'description' => 'Die moderne Arbeitsplattform für Tankstellenpartner. Digital, sicher und übersichtlich.',
                'columns' => [
                    ['title' => 'Produkt', 'links' => [['label' => 'Funktionen', 'url' => '#funktionen'], ['label' => 'Preise', 'url' => '#preise']]],
                    ['title' => 'Rechtliches', 'links' => [['label' => 'Datenschutz', 'url' => '#datenschutz'], ['label' => 'Impressum', 'url' => '#']]],
                    ['title' => 'Kontakt', 'links' => [['label' => 'Support', 'url' => 'mailto:support@example.de'], ['label' => 'E-Mail', 'url' => 'mailto:info@example.de']]],
                ],
                'copyright' => '© 2026 Station Desk. Alle Rechte vorbehalten.',
            ],
            'is_published' => true,
        ];
    }
}
