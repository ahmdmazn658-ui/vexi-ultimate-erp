<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductionOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_number', 'product_id', 'bill_of_material_id', 'warehouse_id',
        'quantity_planned', 'quantity_produced', 'status',
        'planned_start_date', 'planned_end_date', 'actual_start_date', 'actual_end_date',
        'notes', 'created_by',
    ];

    protected $casts = [
        'quantity_planned' => 'decimal:2',
        'quantity_produced' => 'decimal:2',
        'planned_start_date' => 'date',
        'planned_end_date' => 'date',
        'actual_start_date' => 'date',
        'actual_end_date' => 'date',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function billOfMaterial(): BelongsTo
    {
        return $this->belongsTo(BillOfMaterial::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }
}
