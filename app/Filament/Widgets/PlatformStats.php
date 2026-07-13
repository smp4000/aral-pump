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
            Stat::make('Partner', Partner::query()->count())
                ->description('Registrierte Mandanten')
                ->descriptionIcon('heroicon-m-building-office-2')
                ->icon('heroicon-o-building-office-2')
                ->color('primary'),
            Stat::make('Tankstellen', Station::query()->count())
                ->description('Standorte auf der Plattform')
                ->descriptionIcon('heroicon-m-map-pin')
                ->icon('heroicon-o-building-storefront')
                ->color('info'),
            Stat::make('Mitarbeiter & Benutzer', User::query()->whereNot('role', 'platform_admin')->count())
                ->description('Partnerzugänge insgesamt')
                ->descriptionIcon('heroicon-m-users')
                ->icon('heroicon-o-user-group')
                ->color('success'),
            Stat::make('Aktive Testphasen', Partner::query()->where('status', 'trial')->where('trial_ends_at', '>', now())->count())
                ->description('30-Tage-Testkonten')
                ->descriptionIcon('heroicon-m-clock')
                ->icon('heroicon-o-sparkles')
                ->color('warning'),
        ];
    }
}
