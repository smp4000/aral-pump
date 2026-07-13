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
        // Bankkonten sind eigenständige, weich löschbare Datensätze. Die
        // Klartextwerte werden durch verschlüsselte Model-Casts geschützt.
        Schema::create('station_bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('station_id')->constrained()->cascadeOnDelete();
            $table->string('account_type')->default('business');
            $table->text('iban');
            $table->char('iban_hash', 64)->index();
            $table->char('iban_last_four', 4)->nullable();
            $table->text('bank_name')->nullable();
            $table->text('bic')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['station_id', 'iban_hash']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('station_bank_accounts');
    }
};
