<?php

namespace Database\Seeders;

use App\Services\SettingsService;
use Illuminate\Database\Seeder;

class ModuleSettingsSeeder extends Seeder
{
    /**
     * Seeds default settings for all modules.
     * Run after initial installation or to reset to defaults.
     */
    public function run(): void
    {
        $service = new SettingsService(null); // System-level settings

        $modules = array_keys(config('module_settings', []));

        foreach ($modules as $module) {
            // Activate core modules by default
            $coreModules = ['accounting', 'e_invoicing', 'finance', 'banking', 'sales', 'purchase', 'inventory', 'hr', 'payroll', 'system'];

            if (in_array($module, $coreModules)) {
                $service->activateModule($module);
            }

            // Initialize default settings for all modules
            $service->initializeDefaults($module);
        }

        $this->command->info('Module settings seeded for ' . count($modules) . ' modules.');
    }
}
