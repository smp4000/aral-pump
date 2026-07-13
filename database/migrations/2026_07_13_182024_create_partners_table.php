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
        // Mandantentabelle: Ein Datensatz entspricht genau einem unabhängigen
        // Tankstellenpartner. Das Konto wird über Status und Laufzeiten gesperrt,
        // ohne die zugehörigen betrieblichen Daten automatisch zu löschen.
        Schema::create('partners', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');
            $table->string('slug')->unique();
            $table->string('status')->default('trial')->index();
            $table->timestamp('trial_ends_at')->nullable()->index();
            $table->timestamp('subscription_ends_at')->nullable();
            $table->timestamp('deactivated_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partners');
    }
};
