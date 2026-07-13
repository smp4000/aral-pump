<?php

namespace App\Filament\Widgets;

use App\Models\Partner;
use App\Models\Station;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Kennzahlen auf dem Dashboard des Plattform-Administrators.
 * Die Werte werden bewusst mandantenübergreifend direkt aus der Datenbank geladen.
 */
class PlatformStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Partner', Partner::query()->count()),
            Stat::make('Tankstellen', Station::query()->count()),
            Stat::make('Mitarbeiter & Benutzer', User::query()->whereNot('role', 'platform_admin')->count()),
            Stat::make('Aktive Testphasen', Partner::query()->where('status', 'trial')->where('trial_ends_at', '>', now())->count())
                ->color('warning'),
        ];
    }
}
