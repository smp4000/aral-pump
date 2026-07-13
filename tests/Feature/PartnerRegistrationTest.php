<?php

namespace Tests\Feature;

use App\Filament\Partner\Pages\Auth\Register;
use App\Models\Partner;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Prüft den ersten vollständigen Geschäftsablauf der Anwendung:
 * Selbstregistrierung, Testphase und rollenabhängiger Panelzugang.
 */
class PartnerRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_landing_login_and_registration_are_available(): void
    {
        $this->get('/')->assertOk()->assertSee('30 Tage kostenlos testen');
        $this->get('/partner/login')->assertOk();
        $this->get('/partner/register')->assertOk();
        $this->get('/admin/login')->assertOk();
    }

    public function test_registration_creates_partner_owner_and_thirty_day_trial(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('partner'));

        Livewire::test(Register::class)
            ->set('data.company_name', 'Muster Tankstellen GmbH')
            ->set('data.name', 'Max Mustermann')
            ->set('data.email', 'max@example.test')
            ->set('data.password', 'Sicheres-Passwort-2026')
            ->set('data.passwordConfirmation', 'Sicheres-Passwort-2026')
            ->set('data.terms_accepted', true)
            ->call('register')
            ->assertHasNoFormErrors();

        $partner = Partner::query()->sole();
        $owner = User::query()->where('email', 'max@example.test')->sole();

        $this->assertSame('trial', $partner->status);
        $this->assertTrue($partner->trial_ends_at->between(now()->addDays(29), now()->addDays(31)));
        $this->assertSame('partner_owner', $owner->role);
        $this->assertTrue($owner->is($partner->users()->first()));
    }

    public function test_expired_trial_is_retained_but_has_no_access(): void
    {
        $partner = Partner::query()->create([
            'company_name' => 'Abgelaufener Partner',
            'slug' => 'abgelaufener-partner',
            'status' => 'trial',
            'trial_ends_at' => now()->subMinute(),
        ]);

        $this->assertDatabaseHas('partners', ['id' => $partner->id]);
        $this->assertFalse($partner->hasActiveAccess());
    }
}
