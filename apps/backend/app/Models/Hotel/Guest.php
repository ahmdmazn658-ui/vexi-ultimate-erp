<?php

namespace App\Models\Hotel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Guest extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'hotel_guests';

    protected $fillable = [
        'full_name', 'email', 'phone', 'nationality', 'id_type', 'id_number', 'notes',
    ];

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'hotel_guest_id');
    }
}
