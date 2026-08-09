<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * رفع الملفات الفعلي في موديول المستندات.
 *
 * - `original_name`: الاسم اللي المستخدم رفع بيه الملف. التخزين بيستخدم اسم عشوائي
 *   (عشان ما يحصلش تصادم أو path traversal)، فالاسم الأصلي لازم يتحفظ لوحده
 *   عشان التحميل يرجّع الملف باسمه المفهوم.
 * - `file_path` كان varchar(255) رغم إن التحقق بيسمح بـ 500 حرف — التوسيع بيصلّح
 *   التعارض ده.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->string('original_name')->nullable()->after('file_path');
            $table->string('file_path', 500)->change();
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn('original_name');
            $table->string('file_path', 255)->change();
        });
    }
};
