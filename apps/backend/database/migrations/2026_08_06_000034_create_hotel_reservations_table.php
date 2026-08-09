<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * الحجز الرئيسي (ممكن يشمل أكتر من غرفة عبر hotel_reservation_rooms).
 * status: tentative (مبدئي) | confirmed | checked_in | checked_out | cancelled | no_show
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotel_reservations', function (Blueprint $table) {
            $table->id();
            $table->string('confirmation_number')->unique();
            $table->foreignId('hotel_guest_id')->constrained('hotel_guests')->cascadeOnDelete();
            $table->foreignId('hotel_channel_id')->nullable()->constrained('hotel_channels')->nullOnDelete();
            $table->date('check_in_date');
            $table->date('check_out_date');
            $table->unsignedTinyInteger('adults')->default(1);
            $table->unsignedTinyInteger('children')->default(0);
            $table->enum('status', [
                'tentative', 'confirmed', 'checked_in', 'checked_out', 'cancelled', 'no_show',
            ])->default('tentative');
            $table->text('special_requests')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['check_in_date', 'check_out_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_reservations');
    }
};
