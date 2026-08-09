<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QualityInspection extends Model
{
    use HasFactory;

    protected $table = 'quality_inspections';

    protected $fillable = [
        'inspection_code', 'project_id', 'subject', 'inspection_date',
        'inspector_id', 'result', 'findings', 'corrective_action',
    ];

    protected $casts = [
        'inspection_date' => 'date',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspector_id');
    }
}
