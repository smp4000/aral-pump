<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Die bestehenden schlanken Stationsdaten werden ohne Datenverlust um
        // die betrieblichen Stammdaten erweitert. Die numerische interne ID
        // bleibt erhalten; die UUID dient als nicht erratbare öffentliche Kennung.
        Schema::table('stations', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->after('id');
            $table->foreignId('brand_id')->nullable()->after('partner_id')->constrained()->nullOnDelete();
            $table->string('sales_channel')->nullable();
            $table->string('ownership_type')->nullable();
            $table->string('district')->nullable();
            $table->text('district_description')->nullable();
            $table->string('region')->nullable();
            $table->string('region_manager')->nullable();
            $table->string('station_number_fuel')->nullable();
            $table->string('station_number_shop')->nullable();
            $table->boolean('has_toll_terminal')->default(false);
            $table->string('house_number', 30)->nullable();
            $table->string('district_part')->nullable();
            $table->string('state')->nullable();
            $table->string('country', 2)->default('DE');
            $table->string('academic_title')->nullable();
            $table->string('contact_first_name')->nullable();
            $table->string('contact_last_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('fax')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->text('tax_id')->nullable();
            $table->text('trade_register')->nullable();
            $table->unsignedSmallInteger('num_pumps')->nullable();
            $table->boolean('has_camera')->default(false);
            $table->boolean('has_shop')->default(true);
            $table->boolean('has_car_wash')->default(false);
            $table->json('opening_hours')->nullable();
            $table->date('first_petrol_sale_date')->nullable();
            $table->date('first_diesel_sale_date')->nullable();
            $table->json('services')->nullable();
            $table->json('fuel_types')->nullable();
            $table->json('additional_businesses')->nullable();
            $table->json('car_wash_details')->nullable();
            $table->decimal('shop_size', 10, 2)->nullable();
            $table->string('shop_type')->nullable();
            $table->string('shop_class')->nullable();
            $table->date('shop_setup_date')->nullable();
            $table->string('nielsen_area')->nullable();
            $table->string('price_region')->nullable();
            $table->string('assortment_level')->nullable();
            $table->string('shop_partner')->nullable();
            $table->string('shop_operation_number')->nullable();
            $table->string('logo_path')->nullable();
            $table->json('photos')->nullable();
            $table->json('competitors')->nullable();
            $table->decimal('price_super', 6, 3)->nullable();
            $table->decimal('price_e10', 6, 3)->nullable();
            $table->decimal('price_diesel', 6, 3)->nullable();
            $table->timestamp('prices_updated_at')->nullable();
            $table->text('device_setup_token')->nullable();
            $table->text('enrollment_token')->nullable();
            $table->json('printer_map')->nullable();
            $table->text('notes')->nullable();
            $table->json('settings')->nullable();
            $table->softDeletes();
        });

        DB::table('stations')->orderBy('id')->each(function (object $station): void {
            $brandId = DB::table('brands')->where('slug', $station->brand)->value('id');

            DB::table('stations')->where('id', $station->id)->update([
                'uuid' => (string) Str::uuid(),
                'brand_id' => $brandId,
            ]);
        });

        Schema::table('stations', function (Blueprint $table) {
            $table->unique('uuid');
            $table->dropColumn('brand');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stations', function (Blueprint $table) {
            $table->string('brand')->default('aral');
        });

        Schema::table('stations', function (Blueprint $table) {
            $table->dropUnique(['uuid']);
            $table->dropConstrainedForeignId('brand_id');
            $table->dropColumn([
                'uuid', 'sales_channel', 'ownership_type', 'district', 'district_description',
                'region', 'region_manager', 'station_number_fuel', 'station_number_shop',
                'has_toll_terminal', 'house_number', 'district_part', 'state', 'country',
                'academic_title', 'contact_first_name', 'contact_last_name', 'phone', 'fax',
                'email', 'website', 'tax_id', 'trade_register', 'num_pumps', 'has_camera',
                'has_shop', 'has_car_wash', 'opening_hours', 'first_petrol_sale_date',
                'first_diesel_sale_date', 'services', 'fuel_types', 'additional_businesses',
                'car_wash_details', 'shop_size', 'shop_type', 'shop_class', 'shop_setup_date',
                'nielsen_area', 'price_region', 'assortment_level', 'shop_partner',
                'shop_operation_number', 'logo_path', 'photos', 'competitors', 'price_super',
                'price_e10', 'price_diesel', 'prices_updated_at', 'device_setup_token',
                'enrollment_token', 'printer_map', 'notes', 'settings', 'deleted_at',
            ]);
        });
    }
};
