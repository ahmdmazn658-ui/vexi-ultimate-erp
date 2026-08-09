<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fleet_maintenance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fleet_vehicle_id')->constrained('fleet_vehicles')->cascadeOnDelete();
            $table->enum('maintenance_type', ['scheduled', 'repair', 'inspection', 'tire_change', 'oil_change', 'other'])
                ->default('scheduled');
            $table->date('service_date');
            $table->unsignedInteger('odometer_km')->nullable();
            $table->decimal('cost', 12, 2)->default(0);
            $table->string('vendor_name')->nullable();
            $table->date('next_due_date')->nullable();
            $table->unsignedInteger('next_due_odometer_km')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fleet_maintenance_records');
    }
};
