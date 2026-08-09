<?php

use App\Http\Controllers\Api\Settings\ModuleSettingsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Settings API Routes
|--------------------------------------------------------------------------
| All settings management endpoints. Protected by auth + admin role.
*/

Route::middleware(['auth:sanctum'])->prefix('v1/settings')->group(function () {

    // Module Management (activation/deactivation)
    Route::get('modules', [ModuleSettingsController::class, 'modules']);
    Route::post('modules/{module}/activate', [ModuleSettingsController::class, 'activateModule'])
        ->middleware('role:admin');
    Route::post('modules/{module}/deactivate', [ModuleSettingsController::class, 'deactivateModule'])
        ->middleware('role:admin');

    // Module Settings CRUD
    Route::get('all', [ModuleSettingsController::class, 'getAllSettings']);
    Route::get('{module}', [ModuleSettingsController::class, 'getModuleSettings']);
    Route::put('{module}', [ModuleSettingsController::class, 'updateModuleSettings'])
        ->middleware('role:admin,manager');
    Route::post('{module}/reset', [ModuleSettingsController::class, 'resetModuleSettings'])
        ->middleware('role:admin');

    // Single setting get/set
    Route::get('{module}/{group}/{key}', [ModuleSettingsController::class, 'getSetting']);
    Route::put('{module}/{group}/{key}', [ModuleSettingsController::class, 'setSetting'])
        ->middleware('role:admin,manager');

    // Import/Export
    Route::get('export/all', [ModuleSettingsController::class, 'exportSettings']);
    Route::post('import', [ModuleSettingsController::class, 'importSettings'])
        ->middleware('role:admin');
});
