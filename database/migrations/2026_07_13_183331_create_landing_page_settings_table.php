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
        // Die Landingpage wird als ein redaktioneller Datensatz gespeichert.
        // JSON-Gruppen halten die einzelnen Seitenabschnitte flexibel, damit
        // neue Karten oder Listen ohne zusätzliche Tabellenspalten möglich sind.
        Schema::create('landing_page_settings', function (Blueprint $table) {
            $table->id();
            $table->json('general');
            $table->json('hero');
            $table->json('features');
            $table->json('steps');
            $table->json('privacy');
            $table->json('pricing');
            $table->json('cta');
            $table->json('footer');
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('landing_page_settings');
    }
};
