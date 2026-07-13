<?php

namespace App\Filament\Resources\Stations\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * Plattformformular einer Tankstelle inklusive Mandant, Marke und GPS-Daten.
 */
class StationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Tankstelle')->columns(2)->schema([
                    Select::make('partner_id')->label('Partner')->relationship('partner', 'company_name')->searchable()->preload()->required(),
                    TextInput::make('name')->label('Bezeichnung')->required(),
                    Select::make('brand')->label('Marke')->options([
                        'aral' => 'Aral', 'shell' => 'Shell', 'esso' => 'Esso',
                        'totalenergies' => 'TotalEnergies', 'independent' => 'Freie Tankstelle',
                    ])->required(),
                    TextInput::make('station_number')->label('Stationsnummer'),
                    TextInput::make('street')->label('Straße'),
                    TextInput::make('postal_code')->label('PLZ'),
                    TextInput::make('city')->label('Ort'),
                    TextInput::make('latitude')->label('Breitengrad')->numeric(),
                    TextInput::make('longitude')->label('Längengrad')->numeric(),
                    TextInput::make('gps_radius_meters')->label('GPS-Radius (Meter)')->numeric()->minValue(20)->default(150)->required(),
                    Toggle::make('is_active')->label('Aktiv')->default(true),
                ]),
            ]);
    }
}
