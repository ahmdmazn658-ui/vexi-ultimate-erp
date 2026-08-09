<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fleet_maintenance_records', function (Blueprint $table) {
            $table->foreignId('journal_entry_id')->nullable()->after('cost')
                ->constrained('journal_entries')->nullOnDelete();
        });

        Schema::table('fleet_fuel_logs', function (Blueprint $table) {
            $table->foreignId('journal_entry_id')->nullable()->after('cost')
                ->constrained('journal_entries')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('fleet_maintenance_records', function (Blueprint $table) {
            $table->dropConstrainedForeignId('journal_entry_id');
        });

        Schema::table('fleet_fuel_logs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('journal_entry_id');
        });
    }
};
