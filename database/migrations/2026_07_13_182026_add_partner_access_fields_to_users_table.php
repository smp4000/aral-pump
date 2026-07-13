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
        // Plattform-Administratoren besitzen keine partner_id. Alle betrieblichen
        // Benutzer werden einem Mandanten zugeordnet und können zentral
        // deaktiviert werden, ohne ihre Historie zu verlieren.
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('partner_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->string('role')->default('employee')->after('password')->index();
            $table->boolean('is_active')->default(true)->after('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('partner_id');
            $table->dropColumn(['role', 'is_active']);
        });
    }
};
