<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * الأدوار. is_system=true للأدوار الأربعة القديمة (admin/manager/employee/
 * accountant) اللي متربطة بعمود users.role — متتحذفش من الواجهة، لكن
 * صلاحياتها قابلة للتعديل. أي دور جديد المستخدم بيعمله is_system=false.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique(); // admin | manager | employee | accountant | custom-slug
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
