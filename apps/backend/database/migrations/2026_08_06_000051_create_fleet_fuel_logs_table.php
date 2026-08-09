<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fleet_fuel_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fleet_vehicle_id')->constrained('fleet_vehicles')->cascadeOnDelete();
            $table->foreignId('fleet_driver_id')->nullable()->constrained('fleet_drivers')->nullOnDelete();
            $table->date('log_date');
            $table->unsignedInteger('odometer_km')->nullable();
            $table->decimal('liters', 8, 2)->nullable();
            $table->decimal('cost', 12, 2)->default(0);
            $table->string('fuel_station')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fleet_fuel_logs');
    }
};
