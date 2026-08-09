<?php

namespace App\Models\Fleet;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Trip extends Model
{
    use HasFactory;

    protected $table = 'fleet_trips';

    protected $fillable = [
        'fleet_vehicle_id', 'fleet_driver_id', 'project_id',
        'purpose', 'origin', 'destination', 'start_at', 'end_at',
        'start_odometer_km', 'end_odometer_km', 'status', 'notes', 'created_by',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'fleet_vehicle_id');
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class, 'fleet_driver_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** المسافة المقطوعة — بترجع null لحد ما الرحلة تتقفل بعداد نهاية. */
    public function getDistanceKmAttribute(): ?int
    {
        if ($this->start_odometer_km === null || $this->end_odometer_km === null) {
            return null;
        }

        return max(0, $this->end_odometer_km - $this->start_odometer_km);
    }
}
