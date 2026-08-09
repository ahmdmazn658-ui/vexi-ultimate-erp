<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * أنواع الغرف (Standard, Deluxe, Suite...) — كل نوع له سعر ليلة افتراضي
 * وسعة نزلاء قصوى. الأسعار الفعلية بتتحدد وقت الحجز (ممكن تختلف بالموسم).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotel_room_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->text('description')->nullable();
            $table->unsignedTinyInteger('max_occupancy')->default(2);
            $table->decimal('base_rate', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_room_types');
    }
};
