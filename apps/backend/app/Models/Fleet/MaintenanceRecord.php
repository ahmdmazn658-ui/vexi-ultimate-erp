<?php

namespace App\Models\Fleet;

use App\Models\JournalEntry;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceRecord extends Model
{
    use HasFactory;

    protected $table = 'fleet_maintenance_records';

    protected $fillable = [
        'fleet_vehicle_id', 'maintenance_type', 'service_date', 'odometer_km',
        'cost', 'journal_entry_id', 'vendor_name', 'next_due_date', 'next_due_odometer_km', 'notes',
    ];

    protected $casts = [
        'service_date' => 'date',
        'next_due_date' => 'date',
        'cost' => 'decimal:2',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'fleet_vehicle_id');
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }
}
