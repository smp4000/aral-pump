<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Speichert die Herkunft einer ausgewählten Tankstelle. Die externe ID
     * ermöglicht später Preisabgleiche und verhindert versehentliche Dubletten.
     */
    public function up(): void
    {
        Schema::table('stations', function (Blueprint $table) {
            $table->string('source_provider', 50)->nullable()->after('station_number');
            $table->string('source_station_id', 100)->nullable()->after('source_provider');
            $table->unique(['partner_id', 'source_provider', 'source_station_id'], 'stations_partner_source_unique');
        });
    }

    public function down(): void
    {
        Schema::table('stations', function (Blueprint $table) {
            $table->dropUnique('stations_partner_source_unique');
            $table->dropColumn(['source_provider', 'source_station_id']);
        });
    }
};
