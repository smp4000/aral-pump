<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Brand;
use App\Models\Partner;
use App\Models\Station;
use App\Models\StationBankAccount;
use App\Models\User;
use Database\Seeders\BrandSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Sichert UUID, Stammdaten-Hilfsmethoden, Verschlüsselung und Auditierung ab.
 */
class StationMasterDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_brand_seeder_installs_complete_ordered_catalog(): void
    {
        $this->seed(BrandSeeder::class);

        $this->assertDatabaseCount('brands', 30);
        $this->assertSame('Aral', Brand::query()->orderBy('sort_order')->first()->name);
        $this->assertSame(99, Brand::query()->where('slug', 'freie-tankstelle')->value('sort_order'));
    }

    public function test_station_receives_public_uuid_and_calculates_distance_and_opening_hours(): void
    {
        $station = $this->createStation([
            'latitude' => 51.4556430,
            'longitude' => 7.0115550,
            'opening_hours' => [
                ['day' => 'monday', 'open' => '22:00', 'close' => '06:00', 'closed' => false],
                ['day' => 'tuesday', 'open' => '06:00', 'close' => '22:00', 'closed' => true],
            ],
        ]);

        $this->assertNotEmpty($station->uuid);
        $this->assertSame('uuid', $station->getRouteKeyName());
        $this->assertSame(0, $station->distanceToMeters(51.4556430, 7.0115550));
        $this->assertSame(8.0, $station->weekly_opening_hours);
    }

    public function test_mde_token_is_encrypted_and_excluded_from_audit_log(): void
    {
        $station = $this->createStation();
        $token = $station->ensureDeviceSetupToken();
        $rawToken = DB::table('stations')->where('id', $station->id)->value('device_setup_token');

        $this->assertStringStartsWith('mde_', $token);
        $this->assertNotSame($token, $rawToken);
        $this->assertSame($token, $station->fresh()->device_setup_token);
        $this->assertFalse(
            AuditLog::query()->get()->contains(
                fn (AuditLog $log): bool => str_contains(json_encode($log->new_values), $token),
            ),
        );
    }

    public function test_bank_account_is_normalized_encrypted_masked_and_audited_without_secrets(): void
    {
        $station = $this->createStation();
        $account = StationBankAccount::query()->create([
            'station_id' => $station->id,
            'account_type' => 'business',
            'iban' => 'DE89 3704 0044 0532 0130 00',
            'bank_name' => 'Musterbank',
            'bic' => 'COBADEFFXXX',
            'description' => 'Hauptkonto',
        ]);

        $rawAccount = DB::table('station_bank_accounts')->where('id', $account->id)->first();

        $this->assertSame('DE89370400440532013000', $account->fresh()->iban);
        $this->assertNotSame('DE89370400440532013000', $rawAccount->iban);
        $this->assertSame('•••• •••• •••• 3000', $account->masked_iban);
        $this->assertSame(hash('sha256', 'DE89370400440532013000'), $rawAccount->iban_hash);
        $this->assertFalse(
            AuditLog::query()->get()->contains(
                fn (AuditLog $log): bool => str_contains(json_encode($log->new_values), 'DE89370400440532013000'),
            ),
        );
    }

    public function test_admin_and_partner_can_open_documented_station_forms(): void
    {
        $this->seed(BrandSeeder::class);
        $admin = User::factory()->create(['role' => 'platform_admin', 'is_active' => true]);

        $this->actingAs($admin)
            ->get('/admin/stations/create')
            ->assertOk()
            ->assertSee('Standort suchen')
            ->assertSee('Exakt')
            ->assertSee('3 km')
            ->assertSee('5 km')
            ->assertSee('10 km')
            ->assertSee('20 km');

        $station = $this->createStation();
        $owner = User::factory()->create([
            'partner_id' => $station->partner_id,
            'role' => 'partner_owner',
            'is_active' => true,
        ]);

        $this->actingAs($owner)
            ->get("/partner/stations/{$station->uuid}/edit")
            ->assertOk()
            ->assertSee('Bankkonten')
            ->assertSee('Zugeordnete Mitarbeiter');
    }

    /** @param array<string, mixed> $attributes */
    private function createStation(array $attributes = []): Station
    {
        $partner = Partner::query()->create([
            'company_name' => 'Testpartner',
            'slug' => 'testpartner-'.str()->random(8),
            'status' => 'trial',
            'trial_ends_at' => now()->addDays(30),
        ]);
        $brand = Brand::query()->firstOrCreate(['slug' => 'aral'], [
            'name' => 'Aral',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        return Station::query()->create(array_merge([
            'partner_id' => $partner->id,
            'brand_id' => $brand->id,
            'name' => 'Testtankstelle',
            'is_active' => true,
        ], $attributes));
    }
}
