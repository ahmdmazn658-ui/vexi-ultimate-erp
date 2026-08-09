<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupplierBill extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'bill_number', 'supplier_invoice_no', 'supplier_id', 'purchase_order_id', 'project_id',
        'bill_date', 'due_date', 'subtotal', 'vat_rate', 'vat_amount', 'total_amount',
        'paid_amount', 'status', 'expense_account_id', 'journal_entry_id', 'notes', 'created_by',
    ];

    protected $casts = [
        'bill_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'vat_rate' => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    protected $appends = ['balance_due', 'payment_status'];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function expenseAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'expense_account_id');
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SupplierBillItem::class);
    }

    public function allocations(): MorphMany
    {
        return $this->morphMany(PaymentAllocation::class, 'allocatable');
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    /** المتبقي على الفاتورة */
    public function getBalanceDueAttribute(): float
    {
        return round((float) $this->total_amount - (float) $this->paid_amount, 2);
    }

    /** unpaid | partial | paid */
    public function getPaymentStatusAttribute(): string
    {
        $paid = (float) $this->paid_amount;

        if ($paid <= 0) {
            return 'unpaid';
        }

        return $paid >= (float) $this->total_amount - 0.009 ? 'paid' : 'partial';
    }

    /** يعيد حساب subtotal / vat_amount / total_amount من البنود. */
    public function recalculateTotals(): void
    {
        $subtotal = (float) $this->items()->sum('line_total');
        $vat = round($subtotal * ((float) $this->vat_rate / 100), 2);

        $this->update([
            'subtotal' => $subtotal,
            'vat_amount' => $vat,
            'total_amount' => $subtotal + $vat,
        ]);
    }

    /** يحدّث paid_amount من إجمالي التخصيصات المرحّلة، ويقفل الفاتورة لو اتسددت. */
    public function refreshPaidAmount(): void
    {
        $paid = (float) PaymentAllocation::query()
            ->where('allocatable_type', self::class)
            ->where('allocatable_id', $this->id)
            ->whereHas('payment', fn ($q) => $q->where('status', 'posted'))
            ->sum('amount');

        $this->paid_amount = $paid;

        if ($this->status === 'approved' && $paid >= (float) $this->total_amount - 0.009) {
            $this->status = 'paid';
        } elseif ($this->status === 'paid' && $paid < (float) $this->total_amount - 0.009) {
            $this->status = 'approved';
        }

        $this->save();
    }
}
