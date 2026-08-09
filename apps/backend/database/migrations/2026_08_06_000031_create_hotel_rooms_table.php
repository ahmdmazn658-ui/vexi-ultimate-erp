<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * الغرف الفعلية. status بيمثل حالة الـ housekeeping + الإشغال مع بعض:
 * vacant_clean | vacant_dirty | occupied_clean | occupied_dirty |
 * out_of_order | out_of_service
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotel_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_room_type_id')->constrained('hotel_room_types')->cascadeOnDelete();
            $table->string('room_number')->unique();
            $table->string('floor')->nullable();
            $table->enum('status', [
                'vacant_clean', 'vacant_dirty', 'occupied_clean',
                'occupied_dirty', 'out_of_order', 'out_of_service',
            ])->default('vacant_clean');
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_rooms');
    }
};
