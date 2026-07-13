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
        // Ein Mitarbeiter kann an mehreren Tankstellen eingesetzt werden.
        // Stationsrolle und Hauptstandort gelten nur innerhalb dieser Zuordnung.
        Schema::create('station_user', function (Blueprint $table) {
            $table->foreignId('station_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('station_role')->default('employee');
            $table->boolean('is_primary')->default(false);
            $table->timestamp('assigned_at')->nullable();
            $table->timestamps();

            $table->primary(['station_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('station_user');
    }
};
