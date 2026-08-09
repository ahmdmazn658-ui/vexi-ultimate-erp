<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Module: budgeting (ميزانيات على مستوى حساب/فترة، مقارنة بالفعلي من journal_entry_lines)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('period'); // e.g. 2026-Q3 or 2026-08
            $table->date('period_start');
            $table->date('period_end');
            $table->foreignId('account_id')->constrained('chart_of_accounts');
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->decimal('budgeted_amount', 15, 2);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
};
