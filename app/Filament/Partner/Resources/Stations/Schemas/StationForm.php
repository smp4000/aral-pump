<?php

namespace App\Filament\Partner\Resources\Stations\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

/**
 * Definiert das Tankstellenformular im Partnerbereich.
 *
 * Umfangreiche Stationsdaten werden bewusst in Registerkarten gegliedert.
 * Dieses Muster dient als Vorlage für alle späteren langen Modulformulare.
 */
class StationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Tankstellendaten')
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Stammdaten')->schema([
                            TextInput::make('name')
                                ->label('Bezeichnung der Tankstelle')
                                ->required()
                                ->maxLength(255),
                            Select::make('brand')
                                ->label('Tankstellenmarke')
                                ->options([
                                    'aral' => 'Aral',
                                    'shell' => 'Shell',
                                    'esso' => 'Esso',
                                    'totalenergies' => 'TotalEnergies',
                                    'independent' => 'Freie Tankstelle',
                                ])
                                ->default('aral')
                                ->required(),
                            TextInput::make('station_number')
                                ->label('Stationsnummer')
                                ->maxLength(100),
                            Toggle::make('is_active')
                                ->label('Tankstelle ist aktiv')
                                ->default(true),
                        ])->columns(2),
                        Tab::make('Adresse')->schema([
                            TextInput::make('street')->label('Straße und Hausnummer')->maxLength(255),
                            TextInput::make('postal_code')->label('Postleitzahl')->maxLength(20),
                            TextInput::make('city')->label('Ort')->maxLength(255),
                        ])->columns(2),
                        Tab::make('GPS & MDE')->schema([
                            TextInput::make('latitude')
                                ->label('Breitengrad')
                                ->numeric()
                                ->helperText('Wird später für die Standortprüfung des MDE-Geräts verwendet.'),
                            TextInput::make('longitude')
                                ->label('Längengrad')
                                ->numeric(),
                            TextInput::make('gps_radius_meters')
                                ->label('Erlaubter GPS-Radius in Metern')
                                ->numeric()
                                ->minValue(20)
                                ->default(150)
                                ->required(),
                        ])->columns(2),
                    ]),
            ]);
    }
}
