<?php

namespace App\Models\Hotel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PosOutlet extends Model
{
    use HasFactory;

    protected $table = 'hotel_pos_outlets';

    protected $fillable = ['name', 'type', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function products(): HasMany
    {
        return $this->hasMany(PosProduct::class, 'hotel_pos_outlet_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(PosOrder::class, 'hotel_pos_outlet_id');
    }
}
