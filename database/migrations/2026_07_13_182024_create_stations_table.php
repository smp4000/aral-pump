<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Tankstellen sind strikt einem Partner zugeordnet. Markenangabe und
        // GPS-Daten bilden die Grundlage für dynamisches Design und MDE-Prüfung.
        Schema::create('stations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('brand')->default('aral');
            $table->string('station_number')->nullable();
            $table->string('street')->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('city')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->unsignedInteger('gps_radius_meters')->default(150);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['partner_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stations');
    }
};
