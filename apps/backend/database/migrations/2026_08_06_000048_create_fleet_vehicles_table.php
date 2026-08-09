<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * مركبات الأسطول. `fixed_asset_id` رابط اختياري لسجل الأصل الثابت المقابل
 * (لو العربية مُتملّكة ومسجّلة كأصل بيتحسبله إهلاك) — الفليت هنا بيتابع
 * التشغيل اليومي (رحلات، صيانة، وقود)، مش الجانب المحاسبي.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fleet_vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('plate_number')->unique();
            $table->enum('vehicle_type', ['car', 'truck', 'van', 'bus', 'heavy_equipment', 'motorcycle', 'other'])
                ->default('car');
            $table->string('make')->nullable();
            $table->string('model')->nullable();
            $table->unsignedSmallInteger('year')->nullable();
            $table->enum('fuel_type', ['petrol', 'diesel', 'electric', 'hybrid'])->default('petrol');
            $table->enum('ownership', ['owned', 'leased', 'rented'])->default('owned');
            $table->enum('status', ['active', 'under_maintenance', 'out_of_service', 'sold', 'disposed'])
                ->default('active');
            $table->unsignedInteger('odometer_km')->default(0);
            $table->foreignId('fixed_asset_id')->nullable()->constrained('fixed_assets')->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->foreignId('assigned_driver_id')->nullable()->constrained('fleet_drivers')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fleet_vehicles');
    }
};
