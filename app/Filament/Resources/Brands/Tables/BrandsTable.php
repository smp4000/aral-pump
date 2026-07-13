<?php

namespace App\Filament\Resources\Brands\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/** Sortierte Markenübersicht; Marken werden deaktiviert statt gelöscht. */
class BrandsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sort_order')->label('Position')->sortable(),
                TextColumn::make('name')->label('Marke')->searchable()->sortable(),
                TextColumn::make('slug')->label('Kennung')->searchable(),
                ColorColumn::make('primary_color')->label('Primär'),
                ColorColumn::make('secondary_color')->label('Sekundär'),
                TextColumn::make('stations_count')->label('Tankstellen')->counts('stations'),
                IconColumn::make('is_active')->label('Aktiv')->boolean(),
            ])
            ->defaultSort('sort_order')
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([]);
    }
}
