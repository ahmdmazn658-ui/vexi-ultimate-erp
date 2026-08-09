<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * سند قبض (type = receipt) من عميل، أو سند صرف (type = payment) لمورد.
 * الترحيل المحاسبي بيتم في PaymentController@store داخل transaction واحدة.
 */
class Payment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'payment_number', 'type', 'customer_id', 'supplier_id', 'bank_account_id',
        'payment_date', 'amount', 'allocated_amount', 'method', 'reference',
        'status', 'journal_entry_id', 'bank_transaction_id', 'notes', 'created_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
        'allocated_amount' => 'decimal:2',
    ];

    protected $appends = ['unallocated_amount'];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function bankTransaction(): BelongsTo
    {
        return $this->belongsTo(BankTransaction::class);
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(PaymentAllocation::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** المبلغ اللي لسه متخصصش على فواتير (دفعة مقدمة / على الحساب) */
    public function getUnallocatedAmountAttribute(): float
    {
        return round((float) $this->amount - (float) $this->allocated_amount, 2);
    }

    public function isReceipt(): bool
    {
        return $this->type === 'receipt';
    }
}
