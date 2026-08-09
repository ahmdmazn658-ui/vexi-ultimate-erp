<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * سائقي الأسطول. ممكن يكون السائق موظف موجود بالفعل (`employee_id`) أو
 * سائق خارجي/متعاقد مالوش سجل موظف — عشان كده الاسم والهاتف متكررين هنا
 * برضه مش بس في جدول الموظفين.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fleet_drivers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->string('full_name');
            $table->string('phone')->nullable();
            $table->string('license_number')->nullable()->unique();
            $table->enum('license_type', ['private', 'heavy', 'public_transport', 'motorcycle'])
                ->default('private');
            $table->date('license_expiry_date')->nullable();
            $table->enum('status', ['active', 'suspended', 'inactive'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fleet_drivers');
    }
};
