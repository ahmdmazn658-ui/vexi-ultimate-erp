<?php

namespace App\Models\Fleet;

use App\Models\FixedAsset;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'fleet_vehicles';

    protected $fillable = [
        'plate_number', 'vehicle_type', 'make', 'model', 'year',
        'fuel_type', 'ownership', 'status', 'odometer_km',
        'fixed_asset_id', 'project_id', 'assigned_driver_id', 'notes',
    ];

    protected $casts = [
        'odometer_km' => 'integer',
        'year' => 'integer',
    ];

    public function fixedAsset(): BelongsTo
    {
        return $this->belongsTo(FixedAsset::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function assignedDriver(): BelongsTo
    {
        return $this->belongsTo(Driver::class, 'assigned_driver_id');
    }

    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class, 'fleet_vehicle_id');
    }

    public function maintenanceRecords(): HasMany
    {
        return $this->hasMany(MaintenanceRecord::class, 'fleet_vehicle_id');
    }

    public function fuelLogs(): HasMany
    {
        return $this->hasMany(FuelLog::class, 'fleet_vehicle_id');
    }

    public function violations(): HasMany
    {
        return $this->hasMany(Violation::class, 'fleet_vehicle_id');
    }
}
