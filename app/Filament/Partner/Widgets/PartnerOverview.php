<?php

namespace App\Filament\Partner\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/** Startkennzahlen des angemeldeten Partners, strikt auf den Mandanten begrenzt. */
class PartnerOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 10;

    protected function getStats(): array
    {
        $partner = auth()->user()?->partner;
        $trialDays = $partner?->trial_ends_at?->isFuture()
            ? (int) now()->diffInDays($partner->trial_ends_at)
            : 0;

        return [
            Stat::make('Meine Tankstellen', $partner?->stations()->count() ?? 0)
                ->description('Verwaltete Standorte')
                ->descriptionIcon('heroicon-m-map-pin')
                ->icon('heroicon-o-building-storefront')
                ->color('primary'),
            Stat::make('Mitarbeiter', $partner?->users()->whereNot('role', 'partner_owner')->count() ?? 0)
                ->description('Aktuell im Partnerkonto')
                ->descriptionIcon('heroicon-m-users')
                ->icon('heroicon-o-user-group')
                ->color('info'),
            Stat::make('Kontostatus', $partner?->status === 'trial' ? 'Testphase' : 'Aktiv')
                ->description($partner?->status === 'trial' ? "Noch {$trialDays} Tage kostenlos" : 'Abonnement aktiv')
                ->descriptionIcon('heroicon-m-check-badge')
                ->icon('heroicon-o-shield-check')
                ->color($trialDays <= 5 ? 'warning' : 'success'),
        ];
    }
}
