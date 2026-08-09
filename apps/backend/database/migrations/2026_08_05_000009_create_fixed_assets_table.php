<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fixed_assets', function (Blueprint $table) {
            $table->id();
            $table->string('asset_code')->unique();
            $table->string('name');
            $table->enum('category', ['heavy_equipment', 'vehicle', 'tool', 'building', 'furniture', 'it_equipment', 'other'])
                ->default('other');
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->date('purchase_date');
            $table->decimal('purchase_cost', 15, 2);
            $table->unsignedTinyInteger('useful_life_years')->default(5);
            $table->decimal('salvage_value', 15, 2)->default(0);
            $table->enum('depreciation_method', ['straight_line', 'declining_balance'])
                ->default('straight_line');
            $table->decimal('accumulated_depreciation', 15, 2)->default(0);
            $table->enum('status', ['active', 'under_maintenance', 'disposed', 'sold'])
                ->default('active');
            $table->string('location')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fixed_assets');
    }
};
