<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Eine einzelne Tankstelle innerhalb eines Partner-Mandanten.
 *
 * Neben den Stammdaten speichert das Model die Marke für das dynamische Design
 * sowie GPS-Koordinaten und Prüfradius für die spätere Anmeldung am MDE-Gerät.
 * Jede Station gehört zwingend genau einem Partner.
 */
class Station extends Model
{
    use HasFactory;

    protected $fillable = [
        'partner_id', 'name', 'brand', 'station_number', 'street',
        'postal_code', 'city', 'latitude', 'longitude',
        'gps_radius_meters', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'is_active' => 'boolean',
        ];
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }
}
