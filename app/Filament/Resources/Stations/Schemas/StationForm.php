<?php

namespace App\Filament\Resources\Stations\Schemas;

use App\Filament\Shared\Schemas\StationForm as SharedStationForm;
use Filament\Schemas\Schema;

/** Plattformformular mit zusätzlicher Auswahl des Partner-Mandanten. */
class StationForm
{
    public static function configure(Schema $schema): Schema
    {
        return SharedStationForm::configure($schema, includePartner: true);
    }
}
