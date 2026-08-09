<?php

namespace App\Models\Retail;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PosSale extends Model
{
    use HasFactory;

    protected $table = 'pos_sales';

    protected $fillable = [
        'pos_register_session_id', 'customer_id', 'invoice_id', 'journal_entry_id',
        'sale_number', 'payment_method', 'subtotal', 'vat_amount', 'total_amount',
        'cost_amount', 'status', 'created_by',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'cost_amount' => 'decimal:2',
    ];

    public function registerSession(): BelongsTo
    {
        return $this->belongsTo(RegisterSession::class, 'pos_register_session_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PosSaleItem::class);
    }
}
