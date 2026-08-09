<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Module: projects / construction
     */
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('project_code')->unique();
            $table->string('name');
            $table->enum('type', ['construction', 'real_estate', 'infrastructure', 'service', 'other'])
                ->default('construction');
            $table->string('client_name')->nullable();
            $table->string('location')->nullable();
            $table->decimal('budget', 15, 2)->default(0);
            $table->decimal('contract_value', 15, 2)->default(0);
            $table->date('start_date')->nullable();
            $table->date('expected_end_date')->nullable();
            $table->date('actual_end_date')->nullable();
            $table->enum('status', [
                'planning', 'in_progress', 'on_hold', 'completed', 'cancelled',
            ])->default('planning');
            $table->unsignedTinyInteger('progress_percent')->default(0);
            $table->foreignId('project_manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
