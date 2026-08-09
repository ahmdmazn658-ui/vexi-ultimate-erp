<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'sku', 'name', 'category', 'unit', 'cost_price', 'sale_price',
        'reorder_level', 'quantity_on_hand', 'is_active',
    ];

    protected $casts = [
        'cost_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'reorder_level' => 'decimal:2',
        'quantity_on_hand' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function isBelowReorderLevel(): bool
    {
        return (float) $this->quantity_on_hand <= (float) $this->reorder_level;
    }
}
