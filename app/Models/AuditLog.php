<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Unveränderlicher Audit-Datensatz für sicherheits- und fachrelevante Änderungen.
 * Für dieses Model werden bewusst keine Update- oder Löschfunktionen angeboten.
 */
class AuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'partner_id', 'user_id', 'auditable_type', 'auditable_id', 'event',
        'old_values', 'new_values', 'url', 'ip_address', 'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }
}
