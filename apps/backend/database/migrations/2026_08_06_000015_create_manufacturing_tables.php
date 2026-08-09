<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Module: manufacturing (BOM + production orders)
     */
    public function up(): void
    {
        Schema::create('bill_of_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products'); // المنتج النهائي (تام الصنع)
            $table->string('name');
            $table->string('version')->default('1.0');
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('bom_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_of_material_id')->constrained('bill_of_materials')->cascadeOnDelete();
            $table->foreignId('component_product_id')->constrained('products'); // خامة/مكوّن
            $table->decimal('quantity', 12, 4); // الكمية المطلوبة لإنتاج وحدة واحدة من المنتج النهائي
            $table->timestamps();
        });

        Schema::create('production_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('product_id')->constrained('products'); // المنتج النهائي المطلوب إنتاجه
            $table->foreignId('bill_of_material_id')->nullable()->constrained('bill_of_materials')->nullOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses');
            $table->decimal('quantity_planned', 12, 2);
            $table->decimal('quantity_produced', 12, 2)->default(0);
            $table->enum('status', ['draft', 'in_progress', 'completed', 'cancelled'])->default('draft');
            $table->date('planned_start_date')->nullable();
            $table->date('planned_end_date')->nullable();
            $table->date('actual_start_date')->nullable();
            $table->date('actual_end_date')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_orders');
        Schema::dropIfExists('bom_items');
        Schema::dropIfExists('bill_of_materials');
    }
};
