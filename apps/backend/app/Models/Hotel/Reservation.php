<?php

namespace App\Models\Hotel;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reservation extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'hotel_reservations';

    protected $fillable = [
        'confirmation_number', 'hotel_guest_id', 'hotel_channel_id',
        'check_in_date', 'check_out_date', 'adults', 'children',
        'status', 'special_requests', 'created_by',
    ];

    protected $casts = [
        'check_in_date' => 'date',
        'check_out_date' => 'date',
    ];

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class, 'hotel_guest_id');
    }

    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class, 'hotel_channel_id');
    }

    public function reservationRooms(): HasMany
    {
        return $this->hasMany(ReservationRoom::class, 'hotel_reservation_id');
    }

    public function rooms()
    {
        return $this->belongsToMany(Room::class, 'hotel_reservation_rooms', 'hotel_reservation_id', 'hotel_room_id')
            ->withPivot('rate_per_night');
    }

    public function folio(): HasOne
    {
        return $this->hasOne(Folio::class, 'hotel_reservation_id');
    }

    public function posOrders(): HasMany
    {
        return $this->hasMany(PosOrder::class, 'hotel_reservation_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** عدد الليالي بين تاريخ الوصول والمغادرة */
    public function getNightsAttribute(): int
    {
        return (int) $this->check_in_date->diffInDays($this->check_out_date);
    }
}
