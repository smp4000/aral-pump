<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Repräsentiert einen rechtlich und fachlich getrennten Tankstellenpartner.
 *
 * Der Partner ist die oberste Mandantenebene der Anwendung. Sämtliche Benutzer,
 * Tankstellen und späteren Modul-Daten werden über diese ID voneinander getrennt.
 * Der Kontostatus steuert, ob ein Partner sich während der Testphase oder mit
 * einem aktiven Abonnement im Partnerbereich anmelden darf.
 */
class Partner extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_name', 'slug', 'status', 'trial_ends_at',
        'subscription_ends_at', 'deactivated_at',
    ];

    protected function casts(): array
    {
        return [
            'trial_ends_at' => 'datetime',
            'subscription_ends_at' => 'datetime',
            'deactivated_at' => 'datetime',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function stations(): HasMany
    {
        return $this->hasMany(Station::class);
    }

    /**
     * Prüft zentral, ob der Mandant derzeit Zugang zur Anwendung hat.
     *
     * Aktive Abonnements dürfen optional ohne Enddatum laufen. Ein Testkonto
     * ist ausschließlich bis zum Ende der 30-tägigen Testphase freigeschaltet.
     * Deaktivierte Konten und abgelaufene Zeiträume liefern immer `false`.
     */
    public function hasActiveAccess(): bool
    {
        if ($this->status === 'active') {
            return $this->subscription_ends_at === null || $this->subscription_ends_at->isFuture();
        }

        return $this->status === 'trial' && $this->trial_ends_at?->isFuture();
    }
}
