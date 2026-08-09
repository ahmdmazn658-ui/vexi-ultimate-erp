<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fleet_trips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fleet_vehicle_id')->constrained('fleet_vehicles')->cascadeOnDelete();
            $table->foreignId('fleet_driver_id')->constrained('fleet_drivers')->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->string('purpose')->nullable();
            $table->string('origin')->nullable();
            $table->string('destination')->nullable();
            $table->dateTime('start_at');
            $table->dateTime('end_at')->nullable();
            $table->unsignedInteger('start_odometer_km')->nullable();
            $table->unsignedInteger('end_odometer_km')->nullable();
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled'])->default('scheduled');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fleet_trips');
    }
};
