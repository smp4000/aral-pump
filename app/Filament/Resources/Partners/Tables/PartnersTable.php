<?php

namespace App\Filament\Resources\Partners\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Plattformweite Partnerübersicht mit den wichtigsten Betriebskennzahlen.
 * Eine Löschaktion wird absichtlich nicht angeboten, da abgelaufene Konten
 * gemäß Fachkonzept deaktiviert und nicht automatisch entfernt werden.
 */
class PartnersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company_name')->label('Partner')->searchable()->sortable(),
                TextColumn::make('status')->label('Status')->badge()->formatStateUsing(fn (string $state): string => match ($state) {
                    'trial' => 'Testphase', 'active' => 'Aktiv', default => 'Deaktiviert',
                })->color(fn (string $state): string => match ($state) {
                    'trial' => 'warning', 'active' => 'success', default => 'danger',
                }),
                TextColumn::make('stations_count')->label('Tankstellen')->counts('stations')->sortable(),
                TextColumn::make('users_count')->label('Benutzer')->counts('users')->sortable(),
                TextColumn::make('trial_ends_at')->label('Testphase bis')->dateTime('d.m.Y H:i')->sortable(),
                TextColumn::make('created_at')->label('Registriert')->dateTime('d.m.Y H:i')->sortable(),
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
