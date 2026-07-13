<?php

namespace App\Filament\Resources\Brands\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/** Formular für Namen, Reihenfolge, Logo und dynamisches Markenfarbschema. */
class BrandForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Markenprofil')->columns(2)->schema([
                    TextInput::make('name')->label('Name')->required()->maxLength(255),
                    TextInput::make('slug')->label('Technische Kennung')->required()->unique(ignoreRecord: true),
                    TextInput::make('sort_order')->label('Sortierung')->numeric()->default(100)->required(),
                    Toggle::make('is_active')->label('Marke ist auswählbar')->default(true),
                    ColorPicker::make('primary_color')->label('Primärfarbe')->required(),
                    ColorPicker::make('secondary_color')->label('Sekundärfarbe')->required(),
                    FileUpload::make('logo_path')->label('Logo')->image()->disk('public')->directory('brands')->visibility('public')->columnSpanFull(),
                ]),
            ]);
    }
}
