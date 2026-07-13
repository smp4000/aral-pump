<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Zentraler Benutzer für Plattform-Administration und Partner-Mandanten.
 *
 * Plattform-Administratoren besitzen bewusst keinen Partner. Partnerinhaber,
 * Vertreter und Stationsleiter werden dagegen immer über `partner_id` einem
 * Mandanten zugeordnet. Die konkrete Sichtbarkeit von Tankstellen für spätere
 * Mitarbeiterrollen wird in einer separaten Zuordnung umgesetzt.
 */
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'partner_id',
        'name',
        'email',
        'password',
        'role',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    /**
     * Entscheidet an einer zentralen Stelle über den Zugang zu Filament-Panels.
     *
     * Dadurch kann ein Partnerkonto deaktiviert werden, ohne Benutzer oder
     * Geschäftsdaten zu löschen. Der Plattformbereich bleibt ausschließlich
     * Benutzern mit der internen Rolle `platform_admin` vorbehalten.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($panel->getId() === 'admin') {
            return $this->role === 'platform_admin';
        }

        return $panel->getId() === 'partner'
            && $this->partner !== null
            && in_array($this->role, ['partner_owner', 'representative', 'station_manager'], true)
            && $this->partner->hasActiveAccess();
    }
}
