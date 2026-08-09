<?php

namespace App\Services;

use App\Models\ModuleSetting;
use App\Models\ModuleActivation;
use Illuminate\Support\Facades\Cache;

class SettingsService
{
    private ?int $tenantId;

    public function __construct(?int $tenantId = null)
    {
        $this->tenantId = $tenantId ?? auth()->user()?->tenant_id ?? (app()->bound(\App\Models\Tenant::class) ? app(\App\Models\Tenant::class)->id : null);
    }

    /**
     * Get a setting value with default fallback
     */
    public function get(string $module, string $key, mixed $default = null, ?string $group = 'general'): mixed
    {
        $cacheKey = "settings:{$this->tenantId}:{$module}:{$group}:{$key}";

        return Cache::remember($cacheKey, 3600, function () use ($module, $key, $group, $default) {
            $setting = ModuleSetting::where('tenant_id', $this->tenantId)
                ->where('module', $module)
                ->where('group', $group)
                ->where('key', $key)
                ->first();

            return $setting ? $setting->casted_value : $default;
        });
    }

    /**
     * Set a setting value
     */
    public function set(string $module, string $key, mixed $value, ?string $group = 'general', ?string $type = null): void
    {
        if ($type === null) {
            $type = match (true) {
                is_bool($value) => 'boolean',
                is_int($value) => 'integer',
                is_float($value) => 'float',
                is_array($value) => 'json',
                default => 'string',
            };
        }

        $storeValue = match ($type) {
            'boolean' => $value ? '1' : '0',
            'json', 'array' => json_encode($value),
            default => (string) $value,
        };

        ModuleSetting::updateOrCreate(
            [
                'tenant_id' => $this->tenantId,
                'module' => $module,
                'group' => $group,
                'key' => $key,
            ],
            [
                'value' => $storeValue,
                'type' => $type,
                'updated_by' => auth()->id(),
            ]
        );

        Cache::forget("settings:{$this->tenantId}:{$module}:{$group}:{$key}");
    }

    /**
     * Get all settings for a module
     */
    public function getAllForModule(string $module): array
    {
        $settings = ModuleSetting::where('tenant_id', $this->tenantId)
            ->where('module', $module)
            ->get();

        $result = [];
        foreach ($settings as $setting) {
            $result[$setting->group][$setting->key] = $setting->casted_value;
        }

        return $result;
    }

    /**
     * Bulk update settings for a module
     */
    public function bulkUpdate(string $module, array $settings): void
    {
        foreach ($settings as $group => $items) {
            if (!is_array($items)) continue;
            foreach ($items as $key => $value) {
                $this->set($module, $key, $value, $group);
            }
        }
    }

    /**
     * Check if a module is active
     */
    public function isModuleActive(string $module): bool
    {
        $activation = ModuleActivation::where('tenant_id', $this->tenantId)
            ->where('module', $module)
            ->first();

        return $activation?->is_active ?? false;
    }

    /**
     * Activate a module
     */
    public function activateModule(string $module, array $features = []): void
    {
        ModuleActivation::updateOrCreate(
            ['tenant_id' => $this->tenantId, 'module' => $module],
            [
                'is_active' => true,
                'is_installed' => true,
                'enabled_features' => $features,
                'activated_at' => now(),
                'activated_by' => auth()->id(),
            ]
        );
    }

    /**
     * Deactivate a module
     */
    public function deactivateModule(string $module): void
    {
        ModuleActivation::where('tenant_id', $this->tenantId)
            ->where('module', $module)
            ->update(['is_active' => false]);
    }

    /**
     * Get all module activations
     */
    public function getModuleActivations(): array
    {
        return ModuleActivation::where('tenant_id', $this->tenantId)
            ->get()
            ->keyBy('module')
            ->toArray();
    }

    /**
     * Initialize default settings for a module from config
     */
    public function initializeDefaults(string $module): void
    {
        $defaults = config("module_settings.{$module}", []);

        foreach ($defaults as $group => $items) {
            if (!is_array($items)) continue;
            foreach ($items as $key => $config) {
                $exists = ModuleSetting::where('tenant_id', $this->tenantId)
                    ->where('module', $module)
                    ->where('group', $group)
                    ->where('key', $key)
                    ->exists();

                if (!$exists) {
                    $this->set(
                        $module,
                        $key,
                        $config['default'] ?? null,
                        $group,
                        $config['type'] ?? 'string'
                    );
                }
            }
        }
    }
}
