<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Module: quality (تفتيشات جودة مرتبطة بالمشاريع/الأصول)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quality_inspections', function (Blueprint $table) {
            $table->id();
            $table->string('inspection_code')->unique();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->string('subject'); // إيه اللي بيتفتش
            $table->date('inspection_date');
            $table->foreignId('inspector_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('result', ['pending', 'passed', 'failed', 'needs_rework'])->default('pending');
            $table->text('findings')->nullable();
            $table->text('corrective_action')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quality_inspections');
    }
};
