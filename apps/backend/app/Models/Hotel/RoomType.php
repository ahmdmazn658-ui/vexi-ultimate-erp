<?php

namespace App\Models\Hotel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RoomType extends Model
{
    use HasFactory;

    protected $table = 'hotel_room_types';

    protected $fillable = [
        'name', 'name_ar', 'description', 'max_occupancy', 'base_rate', 'is_active',
    ];

    protected $casts = [
        'base_rate' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }
}
