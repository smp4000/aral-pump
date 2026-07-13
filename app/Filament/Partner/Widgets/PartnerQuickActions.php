<?php

namespace App\Filament\Partner\Widgets;

use Filament\Widgets\Widget;

/**
 * Zentrale Schnellaktionen für den täglichen Einstieg des Partners.
 * Weitere Module wie CSV-Import oder Mitarbeiterverwaltung können später
 * als zusätzliche Aktionen ergänzt werden, ohne das Dashboard umzubauen.
 */
class PartnerQuickActions extends Widget
{
    protected string $view = 'filament.partner.widgets.partner-quick-actions';

    protected static ?int $sort = 20;

    protected int|string|array $columnSpan = 'full';
}
