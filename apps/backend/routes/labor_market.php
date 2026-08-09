<?php

use App\Http\Controllers\Api\LaborMarket\NitaqatController;
use App\Http\Controllers\Api\LaborMarket\GosiController;
use App\Http\Controllers\Api\LaborMarket\WpsController;
use App\Http\Controllers\Api\LaborMarket\QiwaController;
use App\Http\Controllers\Api\LaborMarket\MuqeemController;
use App\Http\Controllers\Api\LaborMarket\LaborOfficeController;
use App\Http\Controllers\Api\LaborMarket\HrdfController;
use App\Http\Controllers\Api\LaborMarket\MudadController;
use App\Http\Controllers\Api\LaborMarket\AjeerController;
use App\Http\Controllers\Api\LaborMarket\TaqatController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Labor Market API Routes - مسارات سوق العمل
|--------------------------------------------------------------------------
| كل الأنظمة الحكومية السعودية لسوق العمل
*/

Route::middleware(['auth:sanctum'])->prefix('v1/labor-market')->group(function () {

    // ══ نطاقات (Nitaqat / Saudization) ══════════════════════
    Route::prefix('nitaqat')->group(function () {
        Route::get('/', [NitaqatController::class, 'index']);
        Route::get('status', [NitaqatController::class, 'currentStatus']);
        Route::post('simulate', [NitaqatController::class, 'simulate']);
        Route::post('sync', [NitaqatController::class, 'sync'])->middleware('role:admin,hr_manager');
        Route::get('{record}', [NitaqatController::class, 'show']);
    });

    // ══ التأمينات الاجتماعية (GOSI) ═════════════════
    Route::prefix('gosi')->group(function () {
        // Subscriptions
        Route::get('subscriptions', [GosiController::class, 'subscriptions']);
        Route::post('subscriptions', [GosiController::class, 'storeSubscription']);
        Route::get('subscriptions/{subscription}', [GosiController::class, 'showSubscription']);

        // Monthly Submissions
        Route::get('monthly', [GosiController::class, 'monthlySubmissions']);
        Route::post('monthly/generate', [GosiController::class, 'generateMonthlySubmission']);
        Route::post('monthly/{submission}/submit', [GosiController::class, 'submitMonthly'])
            ->middleware('role:admin,accountant');

        // Injury Reports
        Route::get('injuries', [GosiController::class, 'injuries']);
        Route::post('injuries', [GosiController::class, 'storeInjury']);
    });

    // ══ حماية الأجور (WPS) ══════════════════════════
    Route::prefix('wps')->group(function () {
        Route::get('/', [WpsController::class, 'index']);
        Route::post('generate', [WpsController::class, 'generate']);
        Route::get('{wpsFile}', [WpsController::class, 'show']);
        Route::post('{wpsFile}/submit', [WpsController::class, 'submit'])
            ->middleware('role:admin,accountant');
        Route::get('{wpsFile}/download-sif', [WpsController::class, 'downloadSif']);
    });

    // ══ قوى - العقود الإلكترونية (Qiwa) ═════════════
    Route::prefix('qiwa')->group(function () {
        Route::get('contracts', [QiwaController::class, 'index']);
        Route::post('contracts', [QiwaController::class, 'store']);
        Route::get('contracts/{contract}', [QiwaController::class, 'show']);
        Route::post('contracts/{contract}/send-employee', [QiwaController::class, 'sendToEmployee']);
        Route::post('contracts/{contract}/employer-sign', [QiwaController::class, 'employerSign']);
        Route::post('contracts/{contract}/employee-accept', [QiwaController::class, 'employeeAccept']);
        Route::post('contracts/{contract}/amend', [QiwaController::class, 'amend']);
        Route::post('contracts/{contract}/terminate', [QiwaController::class, 'terminate']);
    });

    // ══ المقيم (Muqeem) ════════════════════════════
    Route::prefix('muqeem')->group(function () {
        Route::get('/', [MuqeemController::class, 'index']);
        Route::post('/', [MuqeemController::class, 'store']);
        Route::get('expiring', [MuqeemController::class, 'expiring']);
        Route::get('{record}', [MuqeemController::class, 'show']);
        Route::post('{record}/renew', [MuqeemController::class, 'requestRenewal']);
        Route::post('{record}/exit-reentry', [MuqeemController::class, 'requestExitReentry']);
        Route::post('{record}/final-exit', [MuqeemController::class, 'requestFinalExit']);
        Route::post('{record}/transfer', [MuqeemController::class, 'requestTransfer']);
    });

    // ══ مكتب العمل (Labor Office) ═══════════════════
    Route::prefix('labor-office')->group(function () {
        // Work Permits
        Route::get('permits', [LaborOfficeController::class, 'workPermits']);
        Route::post('permits', [LaborOfficeController::class, 'storeWorkPermit']);

        // Violations
        Route::get('violations', [LaborOfficeController::class, 'violations']);
        Route::post('violations', [LaborOfficeController::class, 'storeViolation']);
        Route::post('violations/{violation}/correct', [LaborOfficeController::class, 'correctViolation']);
        Route::post('violations/{violation}/appeal', [LaborOfficeController::class, 'appealViolation']);

        // MOL Levies (المقابل المالي)
        Route::get('levies', [LaborOfficeController::class, 'levies']);
        Route::post('levies/calculate', [LaborOfficeController::class, 'calculateLevy']);
    });

    // ══ هدف / HRDF (دعم التوظيف) ═══════════════════
    Route::prefix('hrdf')->group(function () {
        Route::get('programs', [HrdfController::class, 'programs']);
        Route::post('programs', [HrdfController::class, 'storeProgram']);
        Route::get('programs/{program}/beneficiaries', [HrdfController::class, 'beneficiaries']);
        Route::post('programs/{program}/enroll', [HrdfController::class, 'enrollBeneficiary']);
        Route::post('programs/{program}/claim', [HrdfController::class, 'submitClaim']);
    });

    // ══ مدد (Mudad) ═══════════════════════════════
    Route::prefix('mudad')->group(function () {
        Route::get('/', [MudadController::class, 'index']);
        Route::post('check-compliance', [MudadController::class, 'checkCompliance']);
        Route::get('{submission}', [MudadController::class, 'show']);
        Route::post('{submission}/submit', [MudadController::class, 'submit']);
    });

    // ══ أجير (Ajeer) ══════════════════════════════
    Route::prefix('ajeer')->group(function () {
        Route::get('contracts', [AjeerController::class, 'index']);
        Route::post('contracts', [AjeerController::class, 'store']);
        Route::get('contracts/{contract}', [AjeerController::class, 'show']);
        Route::post('contracts/{contract}/activate', [AjeerController::class, 'activate']);
        Route::post('contracts/{contract}/complete', [AjeerController::class, 'complete']);
    });

    // ══ طاقات / جدارات (Taqat / Jadarat) ═════════════
    Route::prefix('taqat')->group(function () {
        Route::get('postings', [TaqatController::class, 'index']);
        Route::post('postings', [TaqatController::class, 'store']);
        Route::post('postings/{posting}/publish', [TaqatController::class, 'publish']);
        Route::post('postings/{posting}/close', [TaqatController::class, 'close']);
    });

    // ══ Dashboard الامتثال الشامل ═══════════════════
    Route::get('compliance-dashboard', function () {
        return response()->json([
            'nitaqat' => [
                'current_band' => 'green_mid',
                'saudization_pct' => 25.5,
            ],
            'gosi' => [
                'last_submission' => 'paid',
                'pending_amount' => 0,
            ],
            'wps' => [
                'last_month_status' => 'compliant',
                'compliance_pct' => 100,
            ],
            'mudad' => [
                'status' => 'green',
                'compliance_pct' => 100,
            ],
            'qiwa' => [
                'active_contracts' => 0,
                'pending_renewal' => 0,
            ],
            'muqeem' => [
                'expiring_30_days' => 0,
                'expired' => 0,
            ],
            'levies' => [
                'monthly_amount' => 0,
                'status' => 'paid',
            ],
        ]);
    });
});
