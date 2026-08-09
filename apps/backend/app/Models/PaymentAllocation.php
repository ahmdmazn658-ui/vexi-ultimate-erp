<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * توزيع مبلغ سند على فاتورة بعينها.
 * allocatable = App\Models\Invoice (قبض) أو App\Models\SupplierBill (صرف).
 */
class PaymentAllocation extends Model
{
    protected $fillable = [
        'payment_id', 'allocatable_id', 'allocatable_type', 'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function allocatable(): MorphTo
    {
        return $this->morphTo();
    }
}
