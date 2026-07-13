<?php

namespace App\Filament\Resources\LandingPageSettings\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Übersicht des einzigen Landingpage-Datensatzes mit direktem Bearbeitungszugriff.
 */
class LandingPageSettingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('general.site_name')->label('Produktname'),
                TextColumn::make('general.seo_title')->label('Seitentitel')->limit(60),
                IconColumn::make('is_published')->label('Veröffentlicht')->boolean(),
                TextColumn::make('updated_at')->label('Zuletzt geändert')->dateTime('d.m.Y H:i')->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([]);
    }
}
