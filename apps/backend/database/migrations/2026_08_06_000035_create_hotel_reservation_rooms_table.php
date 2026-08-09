<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * كل غرفة داخل الحجز، بسعر الليلة وقت الحجز (مش سعر النوع الحالي —
 * عشان لو الأسعار اتغيرت بعدين، الحجز القديم يفضل بسعره الأصلي).
 * قيد unique بيمنع تعارض حجزين على نفس الغرفة بنفس الفترة على مستوى الكود؛
 * التحقق الفعلي من التعارض بيتم في ReservationController::store().
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotel_reservation_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_reservation_id')->constrained('hotel_reservations')->cascadeOnDelete();
            $table->foreignId('hotel_room_id')->constrained('hotel_rooms')->restrictOnDelete();
            $table->decimal('rate_per_night', 12, 2);
            $table->timestamps();

            $table->index(['hotel_room_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_reservation_rooms');
    }
};
