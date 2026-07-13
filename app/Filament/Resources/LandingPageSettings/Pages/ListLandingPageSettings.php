<?php

namespace App\Filament\Resources\LandingPageSettings\Pages;

use App\Filament\Resources\LandingPageSettings\LandingPageSettingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLandingPageSettings extends ListRecords
{
    protected static string $resource = LandingPageSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
