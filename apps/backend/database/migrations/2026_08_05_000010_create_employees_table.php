<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_code')->unique();
            $table->string('full_name');
            $table->string('national_id')->nullable()->unique();
            $table->string('position'); // مهندس موقع، محاسب، عامل...
            $table->enum('department', [
                'engineering', 'finance', 'procurement', 'hr', 'operations', 'management', 'other',
            ])->default('other');
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('basic_salary', 12, 2)->default(0);
            $table->date('hire_date');
            $table->date('termination_date')->nullable();
            $table->enum('employment_type', ['full_time', 'part_time', 'contractor', 'daily_wage'])
                ->default('full_time');
            $table->enum('status', ['active', 'on_leave', 'terminated'])->default('active');
            $table->string('phone')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
