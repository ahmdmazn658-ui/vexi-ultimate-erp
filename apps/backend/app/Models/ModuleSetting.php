<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModuleSetting extends Model
{
    protected $fillable = [
        'tenant_id', 'module', 'group', 'key', 'value', 'type', 'description', 'is_system', 'updated_by',
    ];

    protected $casts = [
        'is_system' => 'boolean',
    ];

    public function getCastedValueAttribute()
    {
        return match ($this->type) {
            'boolean' => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $this->value,
            'float' => (float) $this->value,
            'json', 'array' => json_decode($this->value, true),
            default => $this->value,
        };
    }

    public function scopeForModule($query, string $module)
    {
        return $query->where('module', $module);
    }

    public function scopeForGroup($query, string $group)
    {
        return $query->where('group', $group);
    }
}
