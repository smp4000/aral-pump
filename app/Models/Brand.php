<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Zentrale Tankstellenmarke mit eigenem Farbschema und optionalem Logo.
 */
class Brand extends Model
{
    protected $fillable = [
        'name', 'slug', 'primary_color', 'secondary_color', 'logo_path', 'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return ['sort_order' => 'integer', 'is_active' => 'boolean'];
    }

    public function stations(): HasMany
    {
        return $this->hasMany(Station::class);
    }
}
