<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * أوردر POS. لو room_charge=true، بيتحول لبند في الـ folio تلقائيًا
 * (بدل ما يتدفع كاش وقتها) — الحالة الشائعة في الفنادق.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotel_pos_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_pos_outlet_id')->constrained('hotel_pos_outlets')->restrictOnDelete();
            $table->foreignId('hotel_reservation_id')->nullable()->constrained('hotel_reservations')->nullOnDelete();
            $table->foreignId('hotel_room_id')->nullable()->constrained('hotel_rooms')->nullOnDelete();
            $table->boolean('room_charge')->default(false);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->enum('status', ['open', 'paid', 'charged_to_room', 'cancelled'])->default('open');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_pos_orders');
    }
};
