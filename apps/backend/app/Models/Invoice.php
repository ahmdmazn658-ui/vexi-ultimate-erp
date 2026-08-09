<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'invoice_number', 'customer_id', 'sales_order_id', 'invoice_date', 'due_date',
        'subtotal', 'vat_rate', 'vat_amount', 'total_amount', 'paid_amount', 'status',
        'qr_code', 'zatca_uuid', 'journal_entry_id', 'notes', 'created_by',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'vat_rate' => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    protected $appends = ['balance_due', 'payment_status'];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function allocations(): MorphMany
    {
        return $this->morphMany(PaymentAllocation::class, 'allocatable');
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

    /**
     * يحدّث paid_amount من إجمالي سندات القبض المرحّلة المخصّصة على الفاتورة،
     * وينقل الحالة لـ paid لما تتسدد بالكامل (والعكس عند إلغاء سند).
     */
    public function refreshPaidAmount(): void
    {
        $paid = (float) PaymentAllocation::query()
            ->where('allocatable_type', self::class)
            ->where('allocatable_id', $this->id)
            ->whereHas('payment', fn ($q) => $q->where('status', 'posted'))
            ->sum('amount');

        $this->paid_amount = $paid;

        if ($this->status === 'issued' && $paid >= (float) $this->total_amount - 0.009) {
            $this->status = 'paid';
        } elseif ($this->status === 'paid' && $paid < (float) $this->total_amount - 0.009) {
            $this->status = 'issued';
        }

        $this->save();
    }

    /**
     * يعيد حساب subtotal / vat_amount / total_amount من بنود الفاتورة.
     */
    public function recalculateTotals(): void
    {
        $subtotal = $this->items()->sum('line_total');
        $vat = round($subtotal * ((float) $this->vat_rate / 100), 2);

        $this->update([
            'subtotal' => $subtotal,
            'vat_amount' => $vat,
            'total_amount' => $subtotal + $vat,
        ]);
    }
}
