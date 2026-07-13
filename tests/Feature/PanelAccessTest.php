<?php

namespace Tests\Feature;

use App\Filament\Partner\Resources\Stations\StationResource;
use App\Models\Partner;
use App\Models\Station;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Sichert die Trennung zwischen Plattformverwaltung und Partner-Mandanten ab.
 */
class PanelAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_platform_admin_can_open_global_dashboard(): void
    {
        $admin = User::factory()->create([
            'role' => 'platform_admin',
            'is_active' => true,
        ]);

        $this->actingAs($admin)->get('/admin')->assertOk();
    }

    public function test_partner_station_resource_only_returns_own_stations(): void
    {
        $partner = $this->createPartner('erster-partner');
        $otherPartner = $this->createPartner('zweiter-partner');
        $owner = User::factory()->create([
            'partner_id' => $partner->id,
            'role' => 'partner_owner',
            'is_active' => true,
        ]);
        $ownStation = Station::query()->create([
            'partner_id' => $partner->id,
            'name' => 'Eigene Tankstelle',
            'brand' => 'aral',
        ]);
        Station::query()->create([
            'partner_id' => $otherPartner->id,
            'name' => 'Fremde Tankstelle',
            'brand' => 'shell',
        ]);

        $this->actingAs($owner);
        Filament::setCurrentPanel(Filament::getPanel('partner'));

        $this->assertEquals([$ownStation->id], StationResource::getEloquentQuery()->pluck('id')->all());
    }

    private function createPartner(string $slug): Partner
    {
        return Partner::query()->create([
            'company_name' => str($slug)->headline(),
            'slug' => $slug,
            'status' => 'trial',
            'trial_ends_at' => now()->addDays(30),
        ]);
    }
}
