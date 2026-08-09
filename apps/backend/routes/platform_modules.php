<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Platform\PlatformModulesController as C;
Route::middleware('auth:sanctum')->prefix('v1/platform')->group(function () {
 Route::get('catalog',[C::class,'moduleCatalog']);
 Route::get('imports',[C::class,'importJobs']); Route::post('imports',[C::class,'createImport']); Route::post('imports/preview',[C::class,'importPreview']); Route::post('imports/{importJob}/run',[C::class,'runImport']);
 Route::get('exports',[C::class,'exports']); Route::post('exports',[C::class,'createExport']);
 Route::get('workflows',[C::class,'workflows']); Route::post('workflows',[C::class,'createWorkflow']); Route::post('workflows/{workflow}/toggle',[C::class,'toggleWorkflow']);
 Route::get('approvals',[C::class,'approvals']); Route::post('approvals/{approval}/decide',[C::class,'decideApproval']);
 Route::get('integrations',[C::class,'integrations']); Route::post('integrations',[C::class,'createIntegration']); Route::post('integrations/{integration}/test',[C::class,'testIntegration']);
 Route::get('webhooks',[C::class,'webhooks']); Route::post('webhooks',[C::class,'createWebhook']);
 Route::get('scheduled-reports',[C::class,'reports']); Route::post('scheduled-reports',[C::class,'scheduleReport']);
 Route::get('custom-fields',[C::class,'customFields']); Route::post('custom-fields',[C::class,'createCustomField']);
 Route::get('notification-templates',[C::class,'notificationTemplates']); Route::post('notification-templates',[C::class,'createNotificationTemplate']);
 Route::get('audit-logs',[C::class,'auditLogs']);
});
