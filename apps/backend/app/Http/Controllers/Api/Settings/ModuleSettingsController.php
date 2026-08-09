<?php

namespace App\Http\Controllers\Api\Settings;

use App\Http\Controllers\Controller;
use App\Services\SettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ModuleSettingsController extends Controller
{
    private SettingsService $settings;

    public function __construct()
    {
        $this->settings = new SettingsService(auth()->user()?->tenant_id);
    }

    /**
     * Get all available modules and their activation status
     */
    public function modules(): JsonResponse
    {
        $allModules = array_keys(config('module_settings', []));
        $activations = $this->settings->getModuleActivations();

        $result = [];
        foreach ($allModules as $module) {
            $result[] = [
                'module' => $module,
                'is_active' => $activations[$module]['is_active'] ?? false,
                'is_installed' => $activations[$module]['is_installed'] ?? false,
                'version' => $activations[$module]['version'] ?? null,
            ];
        }

        return response()->json(['data' => $result]);
    }

    /**
     * Activate a module
     */
    public function activateModule(Request $request, string $module): JsonResponse
    {
        $features = $request->input('features', []);
        $this->settings->activateModule($module, $features);
        $this->settings->initializeDefaults($module);

        return response()->json(['message' => "Module '{$module}' activated successfully"]);
    }

    /**
     * Deactivate a module
     */
    public function deactivateModule(string $module): JsonResponse
    {
        $this->settings->deactivateModule($module);
        return response()->json(['message' => "Module '{$module}' deactivated"]);
    }

    /**
     * Get settings schema for a specific module (available settings + current values)
     */
    public function getModuleSettings(string $module): JsonResponse
    {
        $schema = config("module_settings.{$module}", []);
        if (empty($schema)) {
            return response()->json(['error' => 'Module not found'], 404);
        }

        $currentValues = $this->settings->getAllForModule($module);

        $result = [];
        foreach ($schema as $group => $items) {
            foreach ($items as $key => $config) {
                $result[$group][$key] = [
                    'type' => $config['type'],
                    'label_ar' => $config['label_ar'],
                    'label_en' => $config['label_en'],
                    'options' => $config['options'] ?? null,
                    'default' => $config['default'],
                    'value' => $currentValues[$group][$key] ?? $config['default'],
                ];
            }
        }

        return response()->json(['module' => $module, 'settings' => $result]);
    }

    /**
     * Update settings for a module (bulk)
     */
    public function updateModuleSettings(Request $request, string $module): JsonResponse
    {
        $schema = config("module_settings.{$module}", []);
        if (empty($schema)) {
            return response()->json(['error' => 'Module not found'], 404);
        }

        $settings = $request->input('settings', []);
        $this->settings->bulkUpdate($module, $settings);

        return response()->json(['message' => 'Settings updated successfully']);
    }

    /**
     * Get a single setting value
     */
    public function getSetting(string $module, string $group, string $key): JsonResponse
    {
        $value = $this->settings->get($module, $key, null, $group);
        return response()->json(['module' => $module, 'group' => $group, 'key' => $key, 'value' => $value]);
    }

    /**
     * Set a single setting value
     */
    public function setSetting(Request $request, string $module, string $group, string $key): JsonResponse
    {
        $value = $request->input('value');
        $this->settings->set($module, $key, $value, $group);

        return response()->json(['message' => 'Setting updated', 'module' => $module, 'key' => $key, 'value' => $value]);
    }

    /**
     * Get all settings (all modules) - for admin dashboard
     */
    public function getAllSettings(): JsonResponse
    {
        $allModules = array_keys(config('module_settings', []));
        $result = [];

        foreach ($allModules as $module) {
            $result[$module] = $this->settings->getAllForModule($module);
        }

        return response()->json(['data' => $result]);
    }

    /**
     * Reset module settings to defaults
     */
    public function resetModuleSettings(string $module): JsonResponse
    {
        \App\Models\ModuleSetting::where('module', $module)->delete();
        $this->settings->initializeDefaults($module);

        return response()->json(['message' => "Settings for '{$module}' reset to defaults"]);
    }

    /**
     * Export settings as JSON
     */
    public function exportSettings(): JsonResponse
    {
        $allModules = array_keys(config('module_settings', []));
        $result = [];

        foreach ($allModules as $module) {
            $result[$module] = $this->settings->getAllForModule($module);
        }

        return response()->json($result);
    }

    /**
     * Import settings from JSON
     */
    public function importSettings(Request $request): JsonResponse
    {
        $settings = $request->input('settings', []);

        foreach ($settings as $module => $moduleSettings) {
            $this->settings->bulkUpdate($module, $moduleSettings);
        }

        return response()->json(['message' => 'Settings imported successfully']);
    }
}
