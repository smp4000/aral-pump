<?php

namespace App\Filament\Partner\Resources\Stations\RelationManagers;

use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/** Ordnet ausschließlich Mitarbeiter desselben Partners einer Tankstelle zu. */
class UsersRelationManager extends RelationManager
{
    protected static string $relationship = 'users';

    protected static ?string $title = 'Zugeordnete Mitarbeiter';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')->label('Mitarbeiter')->searchable(),
                TextColumn::make('email')->label('E-Mail')->searchable(),
                TextColumn::make('pivot.station_role')->label('Stationsrolle')->badge(),
                IconColumn::make('pivot.is_primary')->label('Hauptstandort')->boolean(),
                TextColumn::make('pivot.assigned_at')->label('Zugeordnet am')->dateTime('d.m.Y H:i'),
            ])
            ->headerActions([
                AttachAction::make()
                    ->label('Mitarbeiter zuordnen')
                    ->recordSelectOptionsQuery(fn (Builder $query): Builder => $query
                        ->where('partner_id', $this->getOwnerRecord()->partner_id)
                        ->where('is_active', true))
                    ->preloadRecordSelect()
                    ->schema(fn (AttachAction $action): array => [
                        $action->getRecordSelect(),
                        Select::make('station_role')->label('Stationsrolle')->options([
                            'employee' => 'Mitarbeiter',
                            'station_manager' => 'Stationsleiter',
                            'representative' => 'Vertreter',
                        ])->default('employee')->required(),
                        Toggle::make('is_primary')->label('Hauptstandort'),
                    ])
                    ->mutateDataUsing(function (array $data): array {
                        $data['assigned_at'] = now();

                        return $data;
                    }),
            ])
            ->recordActions([
                DetachAction::make()->label('Zuordnung entfernen'),
            ]);
    }
}
