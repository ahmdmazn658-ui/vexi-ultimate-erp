<?php

namespace App\Models\Fleet;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Driver extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'fleet_drivers';

    protected $fillable = [
        'employee_id', 'full_name', 'phone', 'license_number',
        'license_type', 'license_expiry_date', 'status', 'notes',
    ];

    protected $casts = [
        'license_expiry_date' => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class, 'assigned_driver_id');
    }

    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class, 'fleet_driver_id');
    }

    public function fuelLogs(): HasMany
    {
        return $this->hasMany(FuelLog::class, 'fleet_driver_id');
    }

    public function violations(): HasMany
    {
        return $this->hasMany(Violation::class, 'fleet_driver_id');
    }

    /** الرخصة منتهية؟ بتُستخدم في تنبيهات لوحة الفليت. */
    public function getLicenseExpiredAttribute(): bool
    {
        return (bool) $this->license_expiry_date?->isPast();
    }
}
