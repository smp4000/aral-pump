<?php

namespace App\Filament\Resources\LandingPageSettings;

use App\Filament\Resources\LandingPageSettings\Pages\CreateLandingPageSetting;
use App\Filament\Resources\LandingPageSettings\Pages\EditLandingPageSetting;
use App\Filament\Resources\LandingPageSettings\Pages\ListLandingPageSettings;
use App\Filament\Resources\LandingPageSettings\Schemas\LandingPageSettingForm;
use App\Filament\Resources\LandingPageSettings\Tables\LandingPageSettingsTable;
use App\Models\LandingPageSetting;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

/**
 * Zentrale Admin-Ressource für sämtliche Inhalte der öffentlichen Landingpage.
 *
 * Fachlich existiert genau ein redaktioneller Datensatz. Deshalb wird die
 * Erstellung nach dem ersten Datensatz gesperrt und keine Löschfunktion angeboten.
 */
class LandingPageSettingResource extends Resource
{
    protected static ?string $model = LandingPageSetting::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGlobeAlt;

    protected static ?string $navigationLabel = 'Landingpage';

    protected static ?string $modelLabel = 'Landingpage';

    protected static ?string $pluralModelLabel = 'Landingpage';

    public static function canCreate(): bool
    {
        return ! LandingPageSetting::query()->exists();
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return LandingPageSettingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LandingPageSettingsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLandingPageSettings::route('/'),
            'create' => CreateLandingPageSetting::route('/create'),
            'edit' => EditLandingPageSetting::route('/{record}/edit'),
        ];
    }
}
