<?php

namespace App\Models\Fleet;

use App\Models\JournalEntry;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FuelLog extends Model
{
    use HasFactory;

    protected $table = 'fleet_fuel_logs';

    protected $fillable = [
        'fleet_vehicle_id', 'fleet_driver_id', 'log_date',
        'odometer_km', 'liters', 'cost', 'journal_entry_id', 'fuel_station',
    ];

    protected $casts = [
        'log_date' => 'date',
        'liters' => 'decimal:2',
        'cost' => 'decimal:2',
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
