<?php

namespace App\Filament\Resources\Stations\Pages;

use App\Filament\Resources\Stations\StationResource;
use App\Filament\Shared\Pages\Concerns\HasStationCreationWizard;
use Filament\Resources\Pages\CreateRecord;

class CreateStation extends CreateRecord
{
    use HasStationCreationWizard;

    protected static string $resource = StationResource::class;

    /** Im Plattformbereich gehört die Partnerauswahl zu den Stammdaten. */
    protected function stationWizardIncludesPartnerSelection(): bool
    {
        return true;
    }
}
