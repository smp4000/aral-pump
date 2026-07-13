<?php

namespace App\Filament\Shared\Schemas;

use App\Models\Brand;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

/**
 * Gemeinsames, ausführlich gegliedertes Tankstellenformular für Admin und Partner.
 *
 * Der Plattformbereich blendet zusätzlich die Partnerauswahl ein. Im Partnerpanel
 * wird die Mandanten-ID ausschließlich serverseitig beim Erstellen gesetzt.
 */
class StationForm
{
    public static function configure(Schema $schema, bool $includePartner = false): Schema
    {
        return $schema->components(self::components($includePartner));
    }

    /**
     * Liefert die Formularbestandteile getrennt vom Schema zurück.
     *
     * Dadurch kann dasselbe ausführliche, deutsch beschriftete Tab-Formular
     * sowohl beim normalen Bearbeiten als auch als Schritt des Anlage-Wizards
     * verwendet werden, ohne Felder doppelt zu pflegen.
     *
     * @return array<int, Component>
     */
    public static function components(bool $includePartner = false): array
    {
        $generalFields = [];

        if ($includePartner) {
            $generalFields[] = Select::make('partner_id')
                ->label('Partner')
                ->relationship('partner', 'company_name')
                ->searchable()
                ->preload()
                ->required();
        }

        $generalFields = array_merge($generalFields, [
            TextInput::make('name')->label('Bezeichnung der Tankstelle')->required()->maxLength(255),
            Select::make('brand_id')
                ->label('Tankstellenmarke')
                ->options(fn () => Brand::query()->where('is_active', true)->orderBy('sort_order')->pluck('name', 'id'))
                ->searchable()
                ->preload()
                ->required(),
            TextInput::make('station_number')->label('Allgemeine Stationsnummer')->maxLength(100),
            Select::make('sales_channel')->label('Vertriebskanal')->options([
                'partner' => 'Partnerbetrieb', 'company' => 'Eigengesellschaft', 'dealer' => 'Händler', 'other' => 'Sonstiger',
            ]),
            Select::make('ownership_type')->label('Eigentumsart')->options([
                'owned' => 'Eigentum', 'leased' => 'Pacht', 'rented' => 'Miete', 'agency' => 'Agentur', 'other' => 'Sonstige',
            ]),
            TextInput::make('district')->label('Distrikt'),
            TextInput::make('region')->label('Region'),
            TextInput::make('region_manager')->label('Regionalleitung'),
            Textarea::make('district_description')->label('Beschreibung des Distrikts')->rows(2)->columnSpanFull(),
            TextInput::make('station_number_fuel')->label('Stationsnummer Kraftstoff'),
            TextInput::make('station_number_shop')->label('Stationsnummer Shop'),
            Toggle::make('has_toll_terminal')->label('Mautterminal vorhanden'),
            Toggle::make('is_active')->label('Tankstelle ist aktiv')->default(true),
        ]);

        return [
            Tabs::make('Tankstellen-Stammdaten')
                ->columnSpanFull()
                ->persistTabInQueryString()
                ->tabs([
                    Tab::make('Allgemein')->schema($generalFields)->columns(2),
                    Tab::make('Adresse & GPS')->schema([
                        TextInput::make('street')->label('Straße'),
                        TextInput::make('house_number')->label('Hausnummer'),
                        TextInput::make('postal_code')->label('Postleitzahl'),
                        TextInput::make('city')->label('Ort'),
                        TextInput::make('district_part')->label('Ortsteil'),
                        TextInput::make('state')->label('Bundesland'),
                        TextInput::make('country')->label('Ländercode')->default('DE')->maxLength(2),
                        TextInput::make('latitude')->label('Breitengrad')->numeric(),
                        TextInput::make('longitude')->label('Längengrad')->numeric(),
                        TextInput::make('gps_radius_meters')->label('Erlaubter GPS-Radius in Metern')->numeric()->minValue(20)->default(150)->required(),
                    ])->columns(2),
                    Tab::make('Kontakt & Geschäft')->schema([
                        TextInput::make('academic_title')->label('Titel'),
                        TextInput::make('contact_first_name')->label('Vorname'),
                        TextInput::make('contact_last_name')->label('Nachname'),
                        TextInput::make('phone')->label('Telefon')->tel(),
                        TextInput::make('fax')->label('Fax'),
                        TextInput::make('email')->label('E-Mail')->email(),
                        TextInput::make('website')->label('Webseite')->url(),
                        TextInput::make('tax_id')->label('Steuernummer')->helperText('Wird verschlüsselt gespeichert.'),
                        TextInput::make('trade_register')->label('Handelsregister')->helperText('Wird verschlüsselt gespeichert.'),
                    ])->columns(2),
                    Tab::make('Ausstattung')->schema([
                        TextInput::make('num_pumps')->label('Anzahl Zapfsäulen')->numeric()->minValue(0),
                        Toggle::make('has_camera')->label('Kameraanlage'),
                        Toggle::make('has_shop')->label('Shop vorhanden')->default(true),
                        Toggle::make('has_car_wash')->label('Waschanlage vorhanden'),
                        TagsInput::make('services')->label('Services')->columnSpanFull(),
                        TagsInput::make('fuel_types')->label('Kraftstoffarten')->columnSpanFull(),
                        TagsInput::make('additional_businesses')->label('Zusatzgeschäfte')->columnSpanFull(),
                        KeyValue::make('car_wash_details')->label('Angaben zur Waschanlage')->columnSpanFull(),
                    ])->columns(2),
                    Tab::make('Öffnungszeiten')->schema([
                        Repeater::make('opening_hours')
                            ->label('Regelmäßige Öffnungszeiten')
                            ->schema([
                                Select::make('day')->label('Wochentag')->options(self::weekdays())->required(),
                                TimePicker::make('open')->label('Öffnet')->seconds(false),
                                TimePicker::make('close')->label('Schließt')->seconds(false),
                                Toggle::make('closed')->label('Geschlossen'),
                            ])
                            ->columns(4)
                            ->default(self::defaultOpeningHours())
                            ->reorderable(false)
                            ->columnSpanFull(),
                        DatePicker::make('first_petrol_sale_date')->label('Erster Benzinverkauf')->helperText('Optionale historische Information.'),
                        DatePicker::make('first_diesel_sale_date')->label('Erster Dieselverkauf')->helperText('Optionale historische Information.'),
                    ])->columns(2),
                    Tab::make('Shop')->schema([
                        TextInput::make('shop_size')->label('Shopfläche in m²')->numeric(),
                        TextInput::make('shop_type')->label('Shoptyp'),
                        TextInput::make('shop_class')->label('Shopklasse'),
                        DatePicker::make('shop_setup_date')->label('Einrichtungsdatum'),
                        TextInput::make('nielsen_area')->label('Nielsen-Gebiet'),
                        TextInput::make('price_region')->label('Preisregion'),
                        TextInput::make('assortment_level')->label('Sortimentsstufe'),
                        TextInput::make('shop_partner')->label('Shop-Partner'),
                        TextInput::make('shop_operation_number')->label('Shop-Betriebsnummer'),
                    ])->columns(2),
                    Tab::make('Medien')->schema([
                        FileUpload::make('logo_path')->label('Stationslogo')->image()->disk('public')->directory('stations/logos')->visibility('public'),
                        FileUpload::make('photos')->label('Fotos')->image()->multiple()->reorderable()->disk('public')->directory('stations/photos')->visibility('public')->columnSpanFull(),
                    ])->columns(2),
                    Tab::make('Wettbewerb & Preise')->schema([
                        Repeater::make('competitors')->label('Wettbewerber')->schema([
                            TextInput::make('name')->label('Name')->required(),
                            TextInput::make('distance_km')->label('Entfernung in km')->numeric(),
                            TextInput::make('notes')->label('Hinweis'),
                        ])->columns(3)->columnSpanFull(),
                        TextInput::make('price_super')->label('Superpreis')->numeric(),
                        TextInput::make('price_e10')->label('E10-Preis')->numeric(),
                        TextInput::make('price_diesel')->label('Dieselpreis')->numeric(),
                        DateTimePicker::make('prices_updated_at')->label('Preise aktualisiert am'),
                    ])->columns(2),
                    Tab::make('MDE & Drucker')->schema([
                        TextInput::make('uuid')->label('Öffentliche Stations-UUID')->disabled()->dehydrated(false),
                        KeyValue::make('printer_map')->label('Druckerzuordnung')->columnSpanFull(),
                    ])->columns(2),
                    Tab::make('Notizen & Einstellungen')->schema([
                        Textarea::make('notes')->label('Interne Notizen')->rows(6)->columnSpanFull(),
                        KeyValue::make('settings')->label('Erweiterte Einstellungen')->columnSpanFull(),
                    ]),
                ]),
        ];
    }

    /** @return array<string, string> */
    private static function weekdays(): array
    {
        return ['monday' => 'Montag', 'tuesday' => 'Dienstag', 'wednesday' => 'Mittwoch', 'thursday' => 'Donnerstag', 'friday' => 'Freitag', 'saturday' => 'Samstag', 'sunday' => 'Sonntag'];
    }

    /** @return array<int, array{day: string, open: string, close: string, closed: bool}> */
    private static function defaultOpeningHours(): array
    {
        return collect(array_keys(self::weekdays()))
            ->map(fn (string $day): array => ['day' => $day, 'open' => '06:00', 'close' => '22:00', 'closed' => false])
            ->all();
    }
}
