<?php

namespace App\Filament\Partner\Resources\Stations;

use App\Filament\Partner\Resources\Stations\Pages\CreateStation;
use App\Filament\Partner\Resources\Stations\Pages\EditStation;
use App\Filament\Partner\Resources\Stations\Pages\ListStations;
use App\Filament\Partner\Resources\Stations\RelationManagers\BankAccountsRelationManager;
use App\Filament\Partner\Resources\Stations\RelationManagers\UsersRelationManager;
use App\Filament\Partner\Resources\Stations\Schemas\StationForm;
use App\Filament\Partner\Resources\Stations\Tables\StationsTable;
use App\Models\Station;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Partneransicht zur Pflege der eigenen Tankstellen.
 *
 * Die Ressource erzwingt die Mandantentrennung bereits in der Datenbankabfrage.
 * Ein angemeldeter Partner kann deshalb weder Datensätze fremder Partner sehen
 * noch deren IDs durch manipulierte URLs öffnen.
 */
class StationResource extends Resource
{
    protected static ?string $model = Station::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    protected static ?string $modelLabel = 'Tankstelle';

    protected static ?string $pluralModelLabel = 'Tankstellen';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('partner_id', auth()->user()->partner_id);
    }

    public static function form(Schema $schema): Schema
    {
        return StationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StationsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            BankAccountsRelationManager::class,
            UsersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStations::route('/'),
            'create' => CreateStation::route('/create'),
            'edit' => EditStation::route('/{record}/edit'),
        ];
    }
}
