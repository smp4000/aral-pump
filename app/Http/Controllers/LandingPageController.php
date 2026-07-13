<?php

namespace App\Http\Controllers;

use App\Models\LandingPageSetting;
use Illuminate\Contracts\View\View;

/**
 * Liefert die öffentliche Landingpage mit den im Adminbereich gepflegten Daten.
 */
class LandingPageController extends Controller
{
    /**
     * Verwendet den veröffentlichten Redaktionsdatensatz. Der Rückfall auf die
     * Standardinhalte hält die Seite auch vor dem ersten Seeding darstellbar.
     */
    public function __invoke(): View
    {
        $settings = LandingPageSetting::query()
            ->where('is_published', true)
            ->first() ?? new LandingPageSetting(LandingPageSetting::defaultContent());

        return view('welcome', ['settings' => $settings]);
    }
}
