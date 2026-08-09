<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_code',
        'name',
        'type',
        'client_name',
        'location',
        'budget',
        'contract_value',
        'start_date',
        'expected_end_date',
        'actual_end_date',
        'status',
        'progress_percent',
        'project_manager_id',
        'description',
    ];

    protected $casts = [
        'budget' => 'decimal:2',
        'contract_value' => 'decimal:2',
        'start_date' => 'date',
        'expected_end_date' => 'date',
        'actual_end_date' => 'date',
    ];

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'project_manager_id');
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    public function properties(): HasMany
    {
        return $this->hasMany(Property::class);
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }
}
