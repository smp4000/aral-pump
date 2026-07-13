<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

/**
 * Verwaltungsformular für sämtliche Benutzerkonten der Plattform.
 *
 * Der Plattform-Administrator kann interne Administratoren und Benutzer eines
 * Partner-Mandanten verwalten. Bei bestehenden Konten wird das Passwort nur
 * geändert, wenn tatsächlich ein neuer Wert eingegeben wurde.
 */
class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('partner_id')
                    ->label('Partner')
                    ->relationship('partner', 'company_name')
                    ->searchable()
                    ->preload()
                    ->helperText('Bei Plattform-Administratoren leer lassen.'),
                TextInput::make('name')
                    ->label('Name')
                    ->required(),
                TextInput::make('email')
                    ->label('E-Mail-Adresse')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true),
                Select::make('role')
                    ->label('Rolle')
                    ->options([
                        'platform_admin' => 'Plattform-Administrator',
                        'partner_owner' => 'Partnerinhaber',
                        'representative' => 'Vertreter',
                        'station_manager' => 'Stationsleiter',
                        'employee' => 'Mitarbeiter',
                    ])
                    ->required(),
                Toggle::make('is_active')->label('Benutzer ist aktiv')->default(true),
                DateTimePicker::make('email_verified_at')->label('E-Mail bestätigt am'),
                TextInput::make('password')
                    ->label('Passwort')
                    ->password()
                    ->revealable()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (?string $state): bool => filled($state)),
            ]);
    }
}
