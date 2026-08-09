<?php

namespace App\Models\Fleet;

use App\Models\JournalEntry;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Violation extends Model
{
    use HasFactory;

    protected $table = 'fleet_violations';

    protected $fillable = [
        'fleet_vehicle_id', 'fleet_driver_id', 'violation_number', 'violation_type',
        'violation_date', 'location', 'amount', 'liability', 'status',
        'paid_date', 'journal_entry_id', 'notes',
    ];

    protected $casts = [
        'violation_date' => 'date',
        'paid_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'fleet_vehicle_id');
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class, 'fleet_driver_id');
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }
}
