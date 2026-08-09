<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * المبلغ المحصّل من الفاتورة. حالة السداد (unpaid / partial / paid) بتتحسب
 * من paid_amount مقابل total_amount عبر Invoice::paymentStatus()، عشان ما نضطرش
 * نعدّل الـ enum بتاع status (اللي بيفضل يمثّل دورة حياة الفاتورة نفسها).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->decimal('paid_amount', 15, 2)->default(0)->after('total_amount');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('paid_amount');
        });
    }
};
