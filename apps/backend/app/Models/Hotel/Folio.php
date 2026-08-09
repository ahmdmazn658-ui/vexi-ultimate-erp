<?php

namespace App\Models\Hotel;

use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Folio extends Model
{
    use HasFactory;

    protected $table = 'hotel_folios';

    protected $fillable = [
        'hotel_reservation_id', 'status', 'invoice_id', 'closed_at',
    ];

    protected $casts = [
        'closed_at' => 'datetime',
    ];

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class, 'hotel_reservation_id');
    }

    public function charges(): HasMany
    {
        return $this->hasMany(FolioCharge::class, 'hotel_folio_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function getTotalAttribute(): float
    {
        return round((float) $this->charges()->sum('amount'), 2);
    }
}
