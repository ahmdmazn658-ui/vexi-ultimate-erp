<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'supplier_code', 'name', 'contact_person', 'phone', 'email',
        'address', 'category', 'rating', 'is_active',
    ];

    protected $casts = [
        'rating' => 'decimal:1',
        'is_active' => 'boolean',
    ];

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function bills(): HasMany
    {
        return $this->hasMany(SupplierBill::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /** إجمالي المستحق للمورد من الفواتير المعتمدة غير المسددة */
    public function outstandingBalance(): float
    {
        return round((float) $this->bills()
            ->where('status', 'approved')
            ->selectRaw('COALESCE(SUM(total_amount - paid_amount), 0) as balance')
            ->value('balance'), 2);
    }
}
