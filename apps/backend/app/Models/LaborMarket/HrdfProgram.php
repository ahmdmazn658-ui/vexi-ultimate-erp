<?php

namespace App\Models\LaborMarket;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HrdfProgram extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
        'is_eligible' => 'boolean',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Employee::class);
    }
    public function beneficiaries() { return $this->hasMany(HrdfBeneficiary::class, 'hrdf_program_id'); }

}

