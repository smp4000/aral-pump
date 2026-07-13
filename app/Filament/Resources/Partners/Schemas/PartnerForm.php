<?php

namespace App\Filament\Resources\Partners\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * Formular für Kontostatus, Testphase und Abonnement eines Partners.
 * Das Deaktivieren verändert nur den Status; Geschäftsdaten werden nicht gelöscht.
 */
class PartnerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Partnerkonto')
                    ->columns(2)
                    ->schema([
                        TextInput::make('company_name')->label('Unternehmen')->required()->maxLength(255),
                        TextInput::make('slug')->label('Kurzname')->required()->unique(ignoreRecord: true),
                        Select::make('status')->options([
                            'trial' => 'Testphase',
                            'active' => 'Aktiv',
                            'suspended' => 'Deaktiviert',
                        ])->required(),
                        DateTimePicker::make('trial_ends_at')->label('Testphase endet am'),
                        DateTimePicker::make('subscription_ends_at')->label('Abonnement endet am'),
                        DateTimePicker::make('deactivated_at')->label('Deaktiviert am'),
                    ]),
            ]);
    }
}
