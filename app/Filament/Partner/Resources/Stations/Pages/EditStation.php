<?php

namespace App\Filament\Partner\Resources\Stations\Pages;

use App\Filament\Partner\Resources\Stations\StationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditStation extends EditRecord
{
    protected static string $resource = StationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
