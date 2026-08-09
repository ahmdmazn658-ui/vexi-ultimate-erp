<?php

namespace App\Models\Hotel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReservationRoom extends Model
{
    use HasFactory;

    protected $table = 'hotel_reservation_rooms';

    protected $fillable = [
        'hotel_reservation_id', 'hotel_room_id', 'rate_per_night',
    ];

    protected $casts = [
        'rate_per_night' => 'decimal:2',
    ];

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class, 'hotel_reservation_id');
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'hotel_room_id');
    }
}
