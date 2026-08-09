<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Module: finance / treasury — المقبوضات والمدفوعات
 *
 * payments            : سند قبض (receipt) من عميل، أو سند صرف (payment) لمورد.
 * payment_allocations : توزيع مبلغ السند على فواتير بعينها (polymorphic:
 *                       Invoice لسندات القبض، SupplierBill لسندات الصرف).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_number')->unique();
            $table->enum('type', ['receipt', 'payment']); // قبض من عميل / صرف لمورد
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->foreignId('bank_account_id')->nullable()->constrained('bank_accounts')->nullOnDelete();
            $table->date('payment_date');
            $table->decimal('amount', 15, 2);
            $table->decimal('allocated_amount', 15, 2)->default(0); // الموزّع على الفواتير
            $table->enum('method', ['bank_transfer', 'cash', 'cheque', 'card', 'other'])
                ->default('bank_transfer');
            $table->string('reference')->nullable(); // رقم الحوالة / الشيك
            $table->enum('status', ['draft', 'posted', 'cancelled'])->default('draft');
            $table->foreignId('journal_entry_id')->nullable()
                ->constrained('journal_entries')->nullOnDelete();
            $table->foreignId('bank_transaction_id')->nullable()
                ->constrained('bank_transactions')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['type', 'status', 'payment_date']);
        });

        Schema::create('payment_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained('payments')->cascadeOnDelete();
            $table->morphs('allocatable'); // App\Models\Invoice | App\Models\SupplierBill
            $table->decimal('amount', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_allocations');
        Schema::dropIfExists('payments');
    }
};
