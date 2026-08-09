<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * فاتورة النزيل المفتوحة (Folio) — بتتجمع فيها كل مصاريف الإقامة (غرفة +
 * POS + خدمات إضافية) لحد الـ checkout، وقتها بتتقفل وبيتولّد منها Invoice
 * حقيقي في نظام المحاسبة (invoices/invoice_items) زي أي فاتورة تانية.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotel_folios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_reservation_id')->constrained('hotel_reservations')->cascadeOnDelete();
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_folios');
    }
};
