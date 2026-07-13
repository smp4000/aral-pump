<?php

namespace App\Filament\Partner\Resources\Stations\Pages;

use App\Filament\Partner\Resources\Stations\StationResource;
use App\Filament\Shared\Pages\Concerns\HasStationCreationWizard;
use Filament\Resources\Pages\CreateRecord;

class CreateStation extends CreateRecord
{
    use HasStationCreationWizard;

    protected static string $resource = StationResource::class;

    /**
     * Überschreibt eine eventuell übermittelte Partner-ID mit dem Mandanten
     * des angemeldeten Benutzers. Dies ist eine wichtige serverseitige Grenze
     * und darf nicht allein durch ein ausgeblendetes Formularfeld ersetzt werden.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['partner_id'] = auth()->user()->partner_id;

        return $data;
    }
}
