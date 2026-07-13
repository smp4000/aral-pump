<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * Vollständige Tankstelle innerhalb eines Partner-Mandanten.
 *
 * Die numerische ID bleibt ein effizienter interner Primärschlüssel. Für URLs,
 * QR-Codes und externe Referenzen wird ausschließlich die nicht erratbare UUID
 * verwendet. Änderungen werden protokolliert und Löschungen zunächst nur weich
 * ausgeführt, damit keine betriebliche Historie verloren geht.
 */
class Station extends Model
{
    use Auditable, HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'partner_id', 'brand_id', 'name', 'station_number', 'source_provider',
        'source_station_id',
        'sales_channel', 'ownership_type', 'district', 'district_description',
        'region', 'region_manager', 'station_number_fuel', 'station_number_shop',
        'has_toll_terminal', 'street', 'house_number', 'postal_code', 'city',
        'district_part', 'state', 'country', 'latitude', 'longitude',
        'gps_radius_meters', 'academic_title', 'contact_first_name',
        'contact_last_name', 'phone', 'fax', 'email', 'website', 'tax_id',
        'trade_register', 'num_pumps', 'has_camera', 'has_shop', 'has_car_wash',
        'opening_hours', 'first_petrol_sale_date', 'first_diesel_sale_date',
        'services', 'fuel_types', 'additional_businesses', 'car_wash_details',
        'shop_size', 'shop_type', 'shop_class', 'shop_setup_date', 'nielsen_area',
        'price_region', 'assortment_level', 'shop_partner', 'shop_operation_number',
        'logo_path', 'photos', 'competitors', 'price_super', 'price_e10',
        'price_diesel', 'prices_updated_at', 'device_setup_token',
        'enrollment_token', 'printer_map', 'notes', 'is_active', 'settings',
    ];

    /** Tokens und sensible Geschäftsdaten dürfen niemals im Audit-Log landen. */
    protected array $auditExclude = [
        'device_setup_token', 'enrollment_token', 'tax_id', 'trade_register',
    ];

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
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'num_pumps' => 'integer',
            'has_camera' => 'boolean',
            'has_shop' => 'boolean',
            'has_car_wash' => 'boolean',
            'has_toll_terminal' => 'boolean',
            'opening_hours' => 'array',
            'first_petrol_sale_date' => 'date',
            'first_diesel_sale_date' => 'date',
            'shop_setup_date' => 'date',
            'services' => 'array',
            'fuel_types' => 'array',
            'additional_businesses' => 'array',
            'car_wash_details' => 'array',
            'photos' => 'array',
            'competitors' => 'array',
            'price_super' => 'decimal:3',
            'price_e10' => 'decimal:3',
            'price_diesel' => 'decimal:3',
            'prices_updated_at' => 'datetime',
            'device_setup_token' => 'encrypted',
            'enrollment_token' => 'encrypted',
            'tax_id' => 'encrypted',
            'trade_register' => 'encrypted',
            'printer_map' => 'array',
            'settings' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function bankAccounts(): HasMany
    {
        return $this->hasMany(StationBankAccount::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot(['station_role', 'is_primary', 'assigned_at'])
            ->withTimestamps();
    }

    /**
     * Erzeugt bei Bedarf den stabilen QR-Setup-Schlüssel für MDE-Geräte.
     * Der Wert wird durch den verschlüsselten Cast nicht im Klartext gespeichert.
     */
    public function ensureDeviceSetupToken(): string
    {
        if (blank($this->device_setup_token)) {
            $this->device_setup_token = 'mde_'.Str::random(48);
            $this->save();
        }

        return $this->device_setup_token;
    }

    /** Ersetzt einen möglicherweise kompromittierten QR-Setup-Schlüssel. */
    public function regenerateDeviceSetupToken(): string
    {
        $this->device_setup_token = 'mde_'.Str::random(48);
        $this->save();

        return $this->device_setup_token;
    }

    /** Erzeugt den getrennten Einrichtungsschlüssel für den Stationsdrucker. */
    public function ensureEnrollmentToken(): string
    {
        if (blank($this->enrollment_token)) {
            $this->enrollment_token = 'enr_'.Str::random(48);
            $this->save();
        }

        return $this->enrollment_token;
    }

    /**
     * Berechnet die Luftlinienentfernung zu einer GPS-Position in Metern.
     * Ohne vollständig hinterlegte Stationskoordinaten ist keine Prüfung möglich.
     */
    public function distanceToMeters(float $latitude, float $longitude): ?int
    {
        if ($this->latitude === null || $this->longitude === null) {
            return null;
        }

        $earthRadius = 6_371_000;
        $stationLatitude = (float) $this->latitude;
        $stationLongitude = (float) $this->longitude;
        $latitudeDifference = deg2rad($latitude - $stationLatitude);
        $longitudeDifference = deg2rad($longitude - $stationLongitude);
        $a = sin($latitudeDifference / 2) ** 2
            + cos(deg2rad($stationLatitude))
            * cos(deg2rad($latitude))
            * sin($longitudeDifference / 2) ** 2;

        return (int) round($earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a)));
    }

    public function getFullAddressAttribute(): string
    {
        $streetLine = trim("{$this->street} {$this->house_number}");

        return trim("{$streetLine}, {$this->postal_code} {$this->city}", ' ,');
    }

    public function getSalutationAddressAttribute(): string
    {
        return collect([
            $this->brand?->name !== 'Freie Tankstelle' ? $this->brand?->name : null,
            'Tankstelle',
            $this->contact_first_name,
            $this->contact_last_name,
        ])->filter()->implode(' ');
    }

    /**
     * Summiert die wöchentlichen Öffnungszeiten. Zeiträume über Mitternacht,
     * beispielsweise 22:00 bis 06:00 Uhr, werden korrekt dem Folgetag zugeordnet.
     */
    public function getWeeklyOpeningHoursAttribute(): ?float
    {
        if (empty($this->opening_hours)) {
            return null;
        }

        $totalMinutes = 0;

        foreach ($this->opening_hours as $hours) {
            if (($hours['closed'] ?? false) || empty($hours['open']) || empty($hours['close'])) {
                continue;
            }

            [$openHour, $openMinute] = array_map('intval', explode(':', $hours['open']));
            [$closeHour, $closeMinute] = array_map('intval', explode(':', $hours['close']));
            $open = ($openHour * 60) + $openMinute;
            $close = ($closeHour * 60) + $closeMinute;

            if ($close <= $open) {
                $close += 24 * 60;
            }

            $totalMinutes += $close - $open;
        }

        return round($totalMinutes / 60, 1);
    }

    public function getLabelTemplateSlug(string $category): ?string
    {
        return data_get($this->settings, "label_templates.{$category}");
    }

    public function setLabelTemplateSlug(string $category, string $slug): void
    {
        $settings = $this->settings ?? [];
        data_set($settings, "label_templates.{$category}", $slug);
        $this->update(['settings' => $settings]);
    }
}
