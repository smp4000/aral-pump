<?php

namespace App\Filament\Resources\AuditLogs\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/** Filter- und durchsuchbare Lesetabelle der protokollierten Änderungen. */
class AuditLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')->label('Zeitpunkt')->dateTime('d.m.Y H:i:s')->sortable(),
                TextColumn::make('partner.company_name')->label('Partner')->placeholder('Plattform')->searchable(),
                TextColumn::make('user.name')->label('Benutzer')->placeholder('System')->searchable(),
                TextColumn::make('event')->label('Ereignis')->badge(),
                TextColumn::make('auditable_type')->label('Datentyp')->formatStateUsing(fn (string $state): string => class_basename($state)),
                TextColumn::make('auditable_id')->label('Datensatz-ID')->searchable(),
                TextColumn::make('new_values')->label('Neue Werte')->formatStateUsing(
                    fn ($state): string => json_encode($state, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '–',
                )->wrap()->limit(120),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
