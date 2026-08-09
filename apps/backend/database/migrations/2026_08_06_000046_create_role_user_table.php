<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * أدوار إضافية للمستخدم (فوق العمود users.role الأساسي اللي فضل زي ما هو
 * للتوافق مع الكود الحالي). ده بيسمح مثلاً لموظف "employee" إنه ياخد
 * صلاحيات إضافية من دور "hotel-front-desk" من غير ما نغيّر دوره الأساسي.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role_user', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->primary(['user_id', 'role_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_user');
    }
};
