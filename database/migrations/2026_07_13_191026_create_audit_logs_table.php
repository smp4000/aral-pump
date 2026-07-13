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
        // Unveränderliche Historie fachlich relevanter Model-Änderungen.
        // Sensible Felder werden vor dem Schreiben durch das Model ausgeschlossen.
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('auditable_type');
            $table->string('auditable_id');
            $table->string('event', 30);
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('url')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['auditable_type', 'auditable_id']);
            $table->index(['partner_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
