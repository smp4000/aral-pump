<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Verschlüsselt gespeichertes Bankkonto einer Tankstelle.
 * Eine Station kann getrennte Geschäfts-, Agentur- und Lottokonten führen.
 */
class StationBankAccount extends Model
{
    use Auditable, HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'station_id', 'account_type', 'iban', 'bank_name', 'bic',
        'description', 'is_active',
    ];

    protected $hidden = ['iban', 'bank_name', 'bic', 'description', 'iban_hash'];

    protected array $auditExclude = [
        'iban', 'iban_hash', 'iban_last_four', 'bank_name', 'bic', 'description',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $account): void {
            $normalizedIban = strtoupper(preg_replace('/\s+/', '', (string) $account->iban));

            $account->iban = $normalizedIban;
            $account->iban_hash = hash('sha256', $normalizedIban);
            $account->iban_last_four = substr($normalizedIban, -4) ?: null;
        });
    }

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    protected function casts(): array
    {
        return [
            'iban' => 'encrypted',
            'bank_name' => 'encrypted',
            'bic' => 'encrypted',
            'description' => 'encrypted',
            'is_active' => 'boolean',
        ];
    }

    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class);
    }

    public function getMaskedIbanAttribute(): string
    {
        return '•••• •••• •••• '.($this->iban_last_four ?: '----');
    }
}
