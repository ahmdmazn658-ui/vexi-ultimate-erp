<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Module: accounting/period-closing — الفترات المحاسبية
 *
 * الفترة المقفلة بتمنع أي قيد مُرحّل جديد أو تعديل على قيد قديم داخلها،
 * والحماية مطبّقة مركزياً في JournalEntryObserver عشان تغطي كل مسارات
 * الترحيل (فواتير، سندات، رواتب، إهلاك، قيود يدوية) من غير ما نكررها.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounting_periods', function (Blueprint $table) {
            $table->id();
            $table->string('name');              // 2026-01
            $table->unsignedSmallInteger('fiscal_year');
            $table->unsignedTinyInteger('period_number'); // 1..12
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->timestamp('closed_at')->nullable();
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['fiscal_year', 'period_number']);
            $table->index(['start_date', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounting_periods');
    }
};
