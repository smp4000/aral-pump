<?php

namespace App\Filament\Partner\Resources\Stations\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Kompakte Übersicht aller Tankstellen des angemeldeten Partners.
 *
 * Die eigentliche Mandantenfilterung erfolgt in der zugehörigen Ressource;
 * diese Klasse kümmert sich ausschließlich um Darstellung und Bedienaktionen.
 */
class StationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Tankstelle')->searchable()->sortable(),
                TextColumn::make('brand.name')->label('Marke')->badge()->sortable(),
                TextColumn::make('station_number')->label('Stationsnummer')->searchable(),
                TextColumn::make('city')->label('Ort')->searchable()->sortable(),
                TextColumn::make('users_count')->label('Mitarbeiter')->counts('users'),
                TextColumn::make('bank_accounts_count')->label('Bankkonten')->counts('bankAccounts'),
                TextColumn::make('gps_radius_meters')->label('GPS-Radius')->suffix(' m'),
                IconColumn::make('is_active')->label('Aktiv')->boolean(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
