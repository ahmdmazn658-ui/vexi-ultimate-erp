<?php

namespace App\Models\Hotel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class FolioCharge extends Model
{
    use HasFactory;

    protected $table = 'hotel_folio_charges';

    protected $fillable = [
        'hotel_folio_id', 'type', 'description', 'amount', 'charge_date',
        'source_type', 'source_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'charge_date' => 'date',
    ];

    public function folio(): BelongsTo
    {
        return $this->belongsTo(Folio::class, 'hotel_folio_id');
    }

    public function source(): MorphTo
    {
        return $this->morphTo();
    }
}
