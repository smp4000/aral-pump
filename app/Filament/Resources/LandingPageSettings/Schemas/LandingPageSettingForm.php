<?php

namespace App\Filament\Resources\LandingPageSettings\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

/**
 * Vollständige redaktionelle Pflege der öffentlichen Landingpage.
 *
 * Jede Registerkarte entspricht einem sichtbaren Seitenabschnitt. Wiederholbare
 * Karten, Schritte, Listen und Footerlinks werden mit Repeatern gepflegt, sodass
 * der Administrator deren Anzahl und Reihenfolge ohne Codeänderung bestimmen kann.
 */
class LandingPageSettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('Landingpage-Inhalte')
                ->columnSpanFull()
                ->persistTabInQueryString()
                ->tabs([
                    Tab::make('Allgemein')->schema([
                        TextInput::make('general.site_name')->label('Produktname')->required(),
                        FileUpload::make('general.logo_path')
                            ->label('Logo')
                            ->image()
                            ->disk('public')
                            ->directory('landing')
                            ->visibility('public'),
                        TextInput::make('general.seo_title')->label('SEO-Titel')->required()->columnSpanFull(),
                        Textarea::make('general.seo_description')->label('SEO-Beschreibung')->rows(3)->columnSpanFull(),
                        TextInput::make('general.nav_features')->label('Navigation: Funktionen'),
                        TextInput::make('general.nav_process')->label('Navigation: Ablauf'),
                        TextInput::make('general.nav_pricing')->label('Navigation: Preise'),
                        TextInput::make('general.login_label')->label('Button: Anmeldung'),
                        TextInput::make('general.register_label')->label('Button: Registrierung'),
                        Toggle::make('is_published')->label('Landingpage veröffentlicht')->default(true),
                    ])->columns(2),

                    Tab::make('Hero')->schema([
                        TextInput::make('hero.badge')->label('Hinweis über der Überschrift')->columnSpanFull(),
                        TextInput::make('hero.title_before')->label('Überschrift vor Hervorhebung')->required(),
                        TextInput::make('hero.highlight')->label('Blau hervorgehobener Text')->required(),
                        Textarea::make('hero.description')->label('Beschreibung')->rows(3)->columnSpanFull(),
                        TextInput::make('hero.primary_label')->label('Primärer Button'),
                        TextInput::make('hero.secondary_label')->label('Sekundärer Button'),
                        TagsInput::make('hero.trust_items')->label('Vertrauensmerkmale')->columnSpanFull(),
                        Repeater::make('hero.stats')
                            ->label('Dashboard-Kennzahlen')
                            ->schema([
                                TextInput::make('label')->label('Bezeichnung')->required(),
                                TextInput::make('value')->label('Wert')->required(),
                                TextInput::make('note')->label('Zusatz'),
                                Select::make('tone')->label('Farbe')->options(self::tones())->default('blue'),
                            ])
                            ->columns(4)
                            ->reorderable()
                            ->collapsible()
                            ->columnSpanFull(),
                    ])->columns(2),

                    Tab::make('Funktionen')->schema([
                        TextInput::make('features.kicker')->label('Kleine Überschrift'),
                        TextInput::make('features.title')->label('Hauptüberschrift')->required(),
                        Textarea::make('features.description')->label('Beschreibung')->rows(3)->columnSpanFull(),
                        Repeater::make('features.items')
                            ->label('Funktionskarten')
                            ->schema([
                                TextInput::make('title')->label('Titel')->required(),
                                TextInput::make('icon')->label('Icon-Kennung')->helperText('Zum Beispiel building, users, document oder calendar.'),
                                Textarea::make('description')->label('Beschreibung')->rows(3)->columnSpanFull(),
                                Select::make('tone')->label('Akzentfarbe')->options(self::tones())->default('blue'),
                            ])
                            ->columns(2)
                            ->reorderable()
                            ->collapsible()
                            ->columnSpanFull(),
                    ])->columns(2),

                    Tab::make('Ablauf')->schema([
                        TextInput::make('steps.kicker')->label('Kleine Überschrift'),
                        TextInput::make('steps.title')->label('Hauptüberschrift')->required(),
                        Repeater::make('steps.items')
                            ->label('Schritte')
                            ->schema([
                                TextInput::make('number')->label('Nummer')->required(),
                                TextInput::make('title')->label('Titel')->required(),
                                Textarea::make('description')->label('Beschreibung')->rows(3)->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->reorderable()
                            ->collapsible()
                            ->columnSpanFull(),
                    ])->columns(2),

                    Tab::make('Datenschutz')->schema([
                        TextInput::make('privacy.kicker')->label('Kleine Überschrift'),
                        TextInput::make('privacy.title')->label('Hauptüberschrift')->required(),
                        Textarea::make('privacy.description')->label('Beschreibung')->rows(3)->columnSpanFull(),
                        Repeater::make('privacy.points')
                            ->label('Datenschutzmerkmale')
                            ->schema([
                                TextInput::make('title')->label('Titel')->required(),
                                Textarea::make('description')->label('Beschreibung')->rows(2),
                            ])
                            ->columns(2)
                            ->reorderable()
                            ->collapsible()
                            ->columnSpanFull(),
                        Repeater::make('privacy.status_items')
                            ->label('Statuskarte')
                            ->schema([
                                TextInput::make('label')->label('Bezeichnung')->required(),
                                TextInput::make('status')->label('Status')->required(),
                            ])
                            ->columns(2)
                            ->reorderable()
                            ->columnSpanFull(),
                    ])->columns(2),

                    Tab::make('Preis & Trial')->schema([
                        TextInput::make('pricing.kicker')->label('Kleine Überschrift'),
                        TextInput::make('pricing.title')->label('Hauptüberschrift')->required(),
                        Textarea::make('pricing.description')->label('Beschreibung')->rows(2)->columnSpanFull(),
                        TextInput::make('pricing.badge')->label('Badge auf der Preisbox'),
                        TextInput::make('pricing.plan_name')->label('Tarifname'),
                        TextInput::make('pricing.price')->label('Preis'),
                        TextInput::make('pricing.price_note')->label('Preiszusatz')->columnSpanFull(),
                        TagsInput::make('pricing.features')->label('Enthaltene Leistungen')->columnSpanFull(),
                        TextInput::make('pricing.button_label')->label('Button-Beschriftung'),
                    ])->columns(2),

                    Tab::make('Abschluss-CTA')->schema([
                        TextInput::make('cta.title')->label('Überschrift')->required()->columnSpanFull(),
                        Textarea::make('cta.description')->label('Beschreibung')->rows(3)->columnSpanFull(),
                        TextInput::make('cta.primary_label')->label('Primärer Button'),
                        TextInput::make('cta.secondary_label')->label('Sekundärer Button'),
                    ])->columns(2),

                    Tab::make('Footer')->schema([
                        Textarea::make('footer.description')->label('Produktbeschreibung')->rows(3)->columnSpanFull(),
                        Repeater::make('footer.columns')
                            ->label('Footer-Spalten')
                            ->schema([
                                TextInput::make('title')->label('Spaltentitel')->required(),
                                Repeater::make('links')
                                    ->label('Links')
                                    ->schema([
                                        TextInput::make('label')->label('Linktext')->required(),
                                        TextInput::make('url')->label('Ziel')->required(),
                                    ])
                                    ->columns(2)
                                    ->reorderable(),
                            ])
                            ->reorderable()
                            ->collapsible()
                            ->columnSpanFull(),
                        TextInput::make('footer.copyright')->label('Copyright-Zeile')->columnSpanFull(),
                    ]),
                ]),
        ]);
    }

    /** @return array<string, string> */
    private static function tones(): array
    {
        return [
            'blue' => 'Aral-Blau',
            'cyan' => 'Hellblau',
            'green' => 'Grün',
            'yellow' => 'Gelb',
            'pink' => 'Rot/Pink',
            'purple' => 'Dunkelblau',
            'indigo' => 'Indigo-Blau',
        ];
    }
}
