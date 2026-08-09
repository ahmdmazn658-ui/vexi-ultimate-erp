<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * بنود الـ Folio: room (مصاريف الغرفة الليلية بتتولّد تلقائيًا)، pos (من
 * أوردرات المطعم/البار)، misc (رسوم يدوية زي minibar أو تلفيات).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotel_folio_charges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_folio_id')->constrained('hotel_folios')->cascadeOnDelete();
            $table->enum('type', ['room', 'pos', 'misc'])->default('misc');
            $table->string('description');
            $table->decimal('amount', 12, 2);
            $table->date('charge_date');
            $table->nullableMorphs('source'); // بيربط بـ HotelPosOrder مثلاً لو type=pos
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_folio_charges');
    }
};
