<?php

namespace App\Filament\Resources\LandingPageSettings\Pages;

use App\Filament\Resources\LandingPageSettings\LandingPageSettingResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLandingPageSetting extends EditRecord
{
    protected static string $resource = LandingPageSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
