<?php

namespace Database\Seeders;

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
        User::query()->updateOrCreate([
            'email' => env('PLATFORM_ADMIN_EMAIL', 'admin@stationdesk.local'),
        ], [
            'name' => env('PLATFORM_ADMIN_NAME', 'Plattform Administrator'),
            'password' => Hash::make(env('PLATFORM_ADMIN_PASSWORD', 'password')),
            'role' => 'platform_admin',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
    }
}
