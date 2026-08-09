<?php

namespace App\Models\Hotel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Channel extends Model
{
    use HasFactory;

    protected $table = 'hotel_channels';

    protected $fillable = [
        'name', 'code', 'provider', 'config', 'commission_rate', 'is_active', 'last_synced_at',
    ];

    protected $casts = [
        'config' => 'encrypted:array', // بيانات اتصال المزوّد (API keys) متشفّرة في الداتابيز
        'commission_rate' => 'decimal:2',
        'is_active' => 'boolean',
        'last_synced_at' => 'datetime',
    ];

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'hotel_channel_id');
    }
}
