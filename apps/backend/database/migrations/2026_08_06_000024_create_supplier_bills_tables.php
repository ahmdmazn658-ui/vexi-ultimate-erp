<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Module: finance / procurement — فواتير الموردين (الذمم الدائنة)
 * بتقفل دورة المشتريات: أمر شراء ← فاتورة مورد ← ترحيل محاسبي ← سداد.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_bills', function (Blueprint $table) {
            $table->id();
            $table->string('bill_number')->unique();
            $table->string('supplier_invoice_no')->nullable(); // رقم الفاتورة عند المورد
            $table->foreignId('supplier_id')->constrained('suppliers');
            $table->foreignId('purchase_order_id')->nullable()->constrained('purchase_orders')->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->date('bill_date');
            $table->date('due_date')->nullable();
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('vat_rate', 5, 2)->default(15.00);
            $table->decimal('vat_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->enum('status', ['draft', 'approved', 'paid', 'cancelled'])->default('draft');
            // حساب المصروف/الأصل المدين في القيد (لو فاضي بيروح للمصروفات العامة 5900)
            $table->foreignId('expense_account_id')->nullable()
                ->constrained('chart_of_accounts')->nullOnDelete();
            $table->foreignId('journal_entry_id')->nullable()
                ->constrained('journal_entries')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'due_date']);
        });

        Schema::create('supplier_bill_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_bill_id')->constrained('supplier_bills')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('item_name');
            $table->string('unit')->nullable();
            $table->decimal('quantity', 12, 2);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('line_total', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_bill_items');
        Schema::dropIfExists('supplier_bills');
    }
};
