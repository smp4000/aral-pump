<?php

namespace Database\Seeders;

use App\Models\LandingPageSetting;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Legt den ersten Plattform-Administrator für die lokale Installation an.
     * Die Zugangsdaten stammen aus Umgebungsvariablen und werden nicht fest in
     * einer produktiven Installation hinterlegt.
     */
    public function run(): void
    {
        $this->call(BrandSeeder::class);

        LandingPageSetting::query()->firstOrCreate([], LandingPageSetting::defaultContent());

        User::query()->updateOrCreate([
            'email' => env('PLATFORM_ADMIN_EMAIL', 'admin@stationdesk.local'),
        ], [
            'name' => env('PLATFORM_ADMIN_NAME', 'Plattform Administrator'),
            'password' => Hash::make(env('PLATFORM_ADMIN_PASSWORD', 'password')),
            'role' => 'platform_admin',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Das Demo-Konto wird nur angelegt, wenn es für die lokale Umgebung
        // ausdrücklich aktiviert wurde. Produktiv bleibt die Option deaktiviert.
        if (filter_var(env('DEMO_PARTNER_ENABLED', false), FILTER_VALIDATE_BOOL)) {
            $partner = Partner::query()->updateOrCreate([
                'slug' => 'demo-partner',
            ], [
                'company_name' => 'StationDesk Demo-Partner',
                'status' => 'trial',
                'trial_ends_at' => now()->addDays(30),
                'deactivated_at' => null,
            ]);

            User::query()->updateOrCreate([
                'email' => env('DEMO_PARTNER_EMAIL', 'partner@stationdesk.local'),
            ], [
                'partner_id' => $partner->id,
                'name' => env('DEMO_PARTNER_NAME', 'Demo Tankstellenpartner'),
                'password' => Hash::make(env('DEMO_PARTNER_PASSWORD', 'password')),
                'role' => 'partner_owner',
                'is_active' => true,
                'email_verified_at' => now(),
            ]);
        }
    }
}
