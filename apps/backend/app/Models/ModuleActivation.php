<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModuleActivation extends Model
{
    protected $fillable = [
        'tenant_id', 'module', 'is_active', 'is_installed', 'version',
        'enabled_features', 'activated_at', 'activated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_installed' => 'boolean',
        'enabled_features' => 'array',
        'activated_at' => 'datetime',
    ];
}
