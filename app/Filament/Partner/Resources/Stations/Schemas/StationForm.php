<?php

namespace App\Filament\Partner\Resources\Stations\Schemas;

use App\Filament\Shared\Schemas\StationForm as SharedStationForm;
use Filament\Schemas\Schema;

/** Partnerformular ohne frei manipulierbare Mandantenauswahl. */
class StationForm
{
    public static function configure(Schema $schema): Schema
    {
        return SharedStationForm::configure($schema);
    }
}
