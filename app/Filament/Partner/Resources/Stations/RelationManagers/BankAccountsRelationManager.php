<?php

namespace App\Filament\Partner\Resources\Stations\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Verwaltet die verschlüsselten Bankkonten einer Tankstelle.
 * In der Tabelle wird aus Gründen der Datenminimierung nur die maskierte IBAN gezeigt.
 */
class BankAccountsRelationManager extends RelationManager
{
    protected static string $relationship = 'bankAccounts';

    protected static ?string $title = 'Bankkonten';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('account_type')->label('Kontotyp')->options([
                'business' => 'Geschäftskonto',
                'agency' => 'Agenturkonto',
                'lottery' => 'Lottokonto',
                'other' => 'Sonstiges Konto',
            ])->required(),
            TextInput::make('iban')
                ->label('IBAN')
                ->required()
                ->maxLength(34)
                ->rule('regex:/^[A-Za-z]{2}[0-9A-Za-z ]{13,32}$/')
                ->helperText('Wird normalisiert und verschlüsselt gespeichert.'),
            TextInput::make('bank_name')->label('Bankname'),
            TextInput::make('bic')->label('BIC')->maxLength(11),
            Textarea::make('description')->label('Beschreibung')->rows(3)->columnSpanFull(),
            Toggle::make('is_active')->label('Konto ist aktiv')->default(true),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('iban_last_four')
            ->columns([
                TextColumn::make('account_type')->label('Kontotyp')->badge()->formatStateUsing(fn (string $state): string => match ($state) {
                    'business' => 'Geschäft', 'agency' => 'Agentur', 'lottery' => 'Lotto', default => 'Sonstiges',
                }),
                TextColumn::make('masked_iban')->label('IBAN'),
                TextColumn::make('bank_name')->label('Bank'),
                IconColumn::make('is_active')->label('Aktiv')->boolean(),
            ])
            ->headerActions([CreateAction::make()->label('Bankkonto anlegen')])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
