<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FixedAsset extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'asset_code', 'name', 'category', 'project_id', 'purchase_date',
        'purchase_cost', 'useful_life_years', 'salvage_value',
        'depreciation_method', 'accumulated_depreciation', 'status', 'location',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'purchase_cost' => 'decimal:2',
        'salvage_value' => 'decimal:2',
        'accumulated_depreciation' => 'decimal:2',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Annual depreciation using straight-line method:
     * (cost - salvage) / useful_life_years
     */
    public function annualDepreciation(): float
    {
        if ($this->depreciation_method !== 'straight_line' || $this->useful_life_years <= 0) {
            return 0.0;
        }

        return round(
            ((float) $this->purchase_cost - (float) $this->salvage_value) / $this->useful_life_years,
            2
        );
    }

    public function bookValue(): float
    {
        return round((float) $this->purchase_cost - (float) $this->accumulated_depreciation, 2);
    }
}
