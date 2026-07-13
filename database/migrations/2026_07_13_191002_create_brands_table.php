<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Marken steuern Bezeichnung, Logo und Farbschema einer Tankstelle.
        // Sie werden zentral gepflegt und können von mehreren Partnern genutzt werden.
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('primary_color', 20)->default('#0050AA');
            $table->string('secondary_color', 20)->default('#00A9E0');
            $table->string('logo_path')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(100)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        // Bereits vorhandene Stationsmarken werden vor dem Umbau der
        // Fremdschlüssel übernommen. Die vollständige Markenliste pflegt danach
        // der BrandSeeder idempotent ein.
        DB::table('stations')->whereNotNull('brand')->distinct()->pluck('brand')->each(
            fn (string $slug) => DB::table('brands')->insert([
                'name' => ucfirst($slug),
                'slug' => $slug,
                'sort_order' => 100,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]),
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brands');
    }
};
