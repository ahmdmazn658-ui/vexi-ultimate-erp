<?php

namespace App\Models\Hotel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PosProduct extends Model
{
    use HasFactory;

    protected $table = 'hotel_pos_products';

    protected $fillable = ['hotel_pos_outlet_id', 'name', 'category', 'price', 'is_active'];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(PosOutlet::class, 'hotel_pos_outlet_id');
    }
}
