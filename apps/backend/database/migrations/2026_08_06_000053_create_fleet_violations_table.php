<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fleet_violations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fleet_vehicle_id')->constrained('fleet_vehicles')->cascadeOnDelete();
            $table->foreignId('fleet_driver_id')->nullable()->constrained('fleet_drivers')->nullOnDelete();
            $table->string('violation_number')->nullable();
            $table->enum('violation_type', [
                'speeding', 'parking', 'red_light', 'no_permit',
                'lane_violation', 'seatbelt', 'phone_use', 'other',
            ])->default('other');
            $table->date('violation_date');
            $table->string('location')->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->enum('liability', ['company', 'driver'])->default('company');
            $table->enum('status', ['unpaid', 'paid', 'disputed', 'waived'])->default('unpaid');
            $table->date('paid_date')->nullable();
            $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fleet_violations');
    }
};
