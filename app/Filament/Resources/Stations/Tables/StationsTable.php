<?php

namespace App\Filament\Resources\Stations\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Plattformweite Tabelle aller Tankstellen über sämtliche Partner hinweg.
 */
class StationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Tankstelle')->searchable()->sortable(),
                TextColumn::make('partner.company_name')->label('Partner')->searchable()->sortable(),
                TextColumn::make('brand.name')->label('Marke')->badge()->sortable(),
                TextColumn::make('city')->label('Ort')->searchable(),
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
