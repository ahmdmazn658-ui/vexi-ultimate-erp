<?php

namespace App\Models\Hotel;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PosOrder extends Model
{
    use HasFactory;

    protected $table = 'hotel_pos_orders';

    protected $fillable = [
        'hotel_pos_outlet_id', 'hotel_reservation_id', 'hotel_room_id',
        'room_charge', 'total_amount', 'status', 'created_by',
    ];

    protected $casts = [
        'room_charge' => 'boolean',
        'total_amount' => 'decimal:2',
    ];

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(PosOutlet::class, 'hotel_pos_outlet_id');
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class, 'hotel_reservation_id');
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'hotel_room_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PosOrderItem::class, 'hotel_pos_order_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function recalculateTotal(): void
    {
        $this->update(['total_amount' => $this->items()->sum('line_total')]);
    }
}
