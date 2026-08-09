<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Property extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_id',
        'property_code',
        'name',
        'property_type',
        'location',
        'area_sqm',
        'price',
        'status',
        'unit_number',
        'bedrooms',
        'bathrooms',
        'description',
    ];

    protected $casts = [
        'area_sqm' => 'decimal:2',
        'price' => 'decimal:2',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
