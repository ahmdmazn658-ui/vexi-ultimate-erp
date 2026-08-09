<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * core/permissions — صلاحيات دقيقة (granular)، كل صلاحية slug بصيغة
 * "module.action" زي accounting.post أو payroll.run. الـ "group" بيُستخدم
 * لتجميع الصلاحيات في واجهة إدارة الأدوار (شاشة واحدة لكل موديول).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique(); // accounting.journal-entries.post
            $table->string('name');
            $table->string('group'); // accounting | hr | sales | hotel ...
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};
