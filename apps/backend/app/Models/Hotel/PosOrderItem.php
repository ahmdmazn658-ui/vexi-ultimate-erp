<?php

namespace App\Models\Hotel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PosOrderItem extends Model
{
    use HasFactory;

    protected $table = 'hotel_pos_order_items';

    protected $fillable = [
        'hotel_pos_order_id', 'hotel_pos_product_id', 'quantity', 'unit_price', 'line_total',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(PosOrder::class, 'hotel_pos_order_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(PosProduct::class, 'hotel_pos_product_id');
    }
}
