<?php

namespace App\Models\Hotel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    use HasFactory;

    protected $table = 'hotel_rooms';

    protected $fillable = [
        'hotel_room_type_id', 'room_number', 'floor', 'status', 'is_active', 'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class, 'hotel_room_type_id');
    }

    public function reservationRooms(): HasMany
    {
        return $this->hasMany(ReservationRoom::class, 'hotel_room_id');
    }

    public function housekeepingTasks(): HasMany
    {
        return $this->hasMany(HousekeepingTask::class, 'hotel_room_id');
    }

    /** فاضية فعليًا خلال الفترة المطلوبة؟ (مفيش حجز متعارض عليها) */
    public function isAvailableBetween(string $checkIn, string $checkOut, ?int $excludeReservationId = null): bool
    {
        return ! $this->reservationRooms()
            ->whereHas('reservation', function ($q) use ($checkIn, $checkOut, $excludeReservationId) {
                $q->whereNotIn('status', ['cancelled', 'no_show', 'checked_out'])
                    ->where('check_in_date', '<', $checkOut)
                    ->where('check_out_date', '>', $checkIn);

                if ($excludeReservationId) {
                    $q->where('id', '!=', $excludeReservationId);
                }
            })
            ->exists();
    }
}
