<?php

use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\AccountingPeriodController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BankAccountController;
use App\Http\Controllers\Api\BankTransactionController;
use App\Http\Controllers\Api\BillOfMaterialController;
use App\Http\Controllers\Api\BudgetController;
use App\Http\Controllers\Api\CandidateController;
use App\Http\Controllers\Api\ContractController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\FixedAssetController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\JobOpeningController;
use App\Http\Controllers\Api\JournalEntryController;
use App\Http\Controllers\Api\LeadController;
use App\Http\Controllers\Api\OpportunityController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\PayrollRunController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProductionOrderController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\PropertyController;
use App\Http\Controllers\Api\PurchaseOrderController;
use App\Http\Controllers\Api\QualityInspectionController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\SalesOrderController;
use App\Http\Controllers\Api\StockMovementController;
use App\Http\Controllers\Api\SupplierBillController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\TicketController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\WarehouseController;
use App\Http\Controllers\Api\Hotel\ChannelController;
use App\Http\Controllers\Api\Hotel\GuestController;
use App\Http\Controllers\Api\Hotel\HousekeepingTaskController;
use App\Http\Controllers\Api\Hotel\PosOrderController;
use App\Http\Controllers\Api\Hotel\PosOutletController;
use App\Http\Controllers\Api\Hotel\PosProductController;
use App\Http\Controllers\Api\Hotel\ReservationController;
use App\Http\Controllers\Api\Hotel\RoomController;
use App\Http\Controllers\Api\Hotel\RoomTypeController;
use App\Http\Controllers\Api\Core\PermissionController;
use App\Http\Controllers\Api\Core\RoleController;
use App\Http\Controllers\Api\Fleet\DriverController as FleetDriverController;
use App\Http\Controllers\Api\Fleet\FuelLogController;
use App\Http\Controllers\Api\Fleet\MaintenanceRecordController;
use App\Http\Controllers\Api\Fleet\TripController;
use App\Http\Controllers\Api\Fleet\VehicleController as FleetVehicleController;
use App\Http\Controllers\Api\Fleet\ViolationController as FleetViolationController;
use App\Http\Controllers\Api\Retail\PosSaleController;
use App\Http\Controllers\Api\Retail\RegisterSessionController;
use Illuminate\Support\Facades\Route;

Route::get('/v1/health', function () {
    try {
        \DB::select('select 1');
        return response()->json(['status' => 'ok', 'service' => 'erp-backend', 'time' => now()->toIso8601String()]);
    } catch (\Throwable $e) {
        return response()->json(['status' => 'degraded', 'service' => 'erp-backend'], 503);
    }
});

Route::get('/v1/ping', fn () => response()->json([
    'status' => 'ok',
    'service' => 'erp-backend',
    'time' => now()->toIso8601String(),
]));

// ── Auth (core/auth) ──────────────────────────────────────────
Route::prefix('v1/auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware(['auth:sanctum', 'tenant'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });
});

// ── Protected business modules ────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // accounting/chart-of-accounts + accounting/journal-entries
    Route::prefix('v1/accounting')->group(function () {
        Route::apiResource('chart-of-accounts', AccountController::class);

        Route::apiResource('journal-entries', JournalEntryController::class)
            ->except(['update']);
        Route::post('journal-entries/{journalEntry}/post', [JournalEntryController::class, 'post']);

        // accounting/period-closing — الفترات المحاسبية والإقفال السنوي
        Route::prefix('periods')->group(function () {
            Route::get('/', [AccountingPeriodController::class, 'index']);
            Route::get('status', [AccountingPeriodController::class, 'status']);

            Route::post('generate', [AccountingPeriodController::class, 'generate'])
                ->middleware('role:admin,accountant');
            Route::post('year-end-closing', [AccountingPeriodController::class, 'yearEndClosing'])
                ->middleware('role:admin,accountant');
            Route::post('{period}/close', [AccountingPeriodController::class, 'close'])
                ->middleware('role:admin,accountant');
            Route::post('{period}/reopen', [AccountingPeriodController::class, 'reopen'])
                ->middleware('role:admin');
        });
    });

    // projects (المشاريع - أساس قطاع المقاولات)
    Route::apiResource('v1/projects', ProjectController::class);

    // contracts (العقود - مرتبطة بالمشاريع)
    Route::apiResource('v1/contracts', ContractController::class);

    // real-estate/properties (العقارات)
    Route::prefix('v1/real-estate')->group(function () {
        Route::apiResource('properties', PropertyController::class);
    });

    // procurement (الموردين + أوامر الشراء)
    Route::prefix('v1/procurement')->group(function () {
        Route::apiResource('suppliers', SupplierController::class);
        Route::apiResource('purchase-orders', PurchaseOrderController::class);

        // فواتير الموردين (الذمم الدائنة) — بتقفل دورة الشراء
        Route::apiResource('supplier-bills', SupplierBillController::class)
            ->parameters(['supplier-bills' => 'supplierBill']);
        Route::post('supplier-bills/from-purchase-order/{purchaseOrder}', [SupplierBillController::class, 'fromPurchaseOrder']);
        Route::post('supplier-bills/{supplierBill}/approve', [SupplierBillController::class, 'approve'])
            ->middleware('role:admin,manager,accountant');
    });

    // finance/treasury — سندات القبض والصرف (تحصيل الفواتير وسداد الموردين)
    Route::prefix('v1/finance')->group(function () {
        Route::apiResource('payments', PaymentController::class)
            ->only(['index', 'store', 'show']);
        Route::post('payments/{payment}/void', [PaymentController::class, 'void'])
            ->middleware('role:admin,accountant');
    });

    // reports/financial — المصدر الوحيد للقوائم المالية (محسوبة من دفتر الأستاذ)
    Route::prefix('v1/reports')->group(function () {
        Route::get('trial-balance', [ReportController::class, 'trialBalance']);
        Route::get('income-statement', [ReportController::class, 'incomeStatement']);
        Route::get('balance-sheet', [ReportController::class, 'balanceSheet']);
        Route::get('general-ledger/{account}', [ReportController::class, 'generalLedger']);
        Route::get('ar-aging', [ReportController::class, 'arAging']);
        Route::get('ap-aging', [ReportController::class, 'apAging']);
    });

    // modules/dashboards — مؤشرات تنفيذية مبنية على نفس محرك التقارير
    Route::get('v1/dashboards/summary', [DashboardController::class, 'summary']);

    // fixed-assets (المعدات والأصول)
    Route::prefix('v1/fixed-assets')->group(function () {
        Route::apiResource('assets', FixedAssetController::class)->parameters(['assets' => 'fixedAsset']);
        Route::post('assets/{fixedAsset}/run-depreciation', [FixedAssetController::class, 'runDepreciation']);
    });

    // hr (الموظفين)
    Route::prefix('v1/hr')->group(function () {
        Route::apiResource('employees', EmployeeController::class);
    });

    // inventory (المخزون: مستودعات + منتجات + حركات مخزون)
    Route::prefix('v1/inventory')->group(function () {
        Route::apiResource('warehouses', WarehouseController::class);
        Route::apiResource('products', ProductController::class);
        Route::apiResource('stock-movements', StockMovementController::class)
            ->only(['index', 'store']);
    });

    // sales (المبيعات: عملاء + طلبات بيع)
    Route::prefix('v1/sales')->group(function () {
        Route::apiResource('customers', CustomerController::class);
        Route::apiResource('orders', SalesOrderController::class)
            ->parameters(['orders' => 'salesOrder']);
        Route::post('orders/{salesOrder}/confirm', [SalesOrderController::class, 'confirm']);
    });

    // e-invoicing (فوترة إلكترونية — ZATCA Phase 1: توليد رقم + QR)
    Route::prefix('v1/e-invoicing')->group(function () {
        Route::apiResource('invoices', InvoiceController::class);
        Route::post('invoices/from-sales-order/{salesOrder}', [InvoiceController::class, 'fromSalesOrder']);
        Route::post('invoices/{invoice}/issue', [InvoiceController::class, 'issue']);
    });

    // manufacturing (BOM + أوامر إنتاج)
    Route::prefix('v1/manufacturing')->group(function () {
        Route::apiResource('bom', BillOfMaterialController::class);
        Route::apiResource('production-orders', ProductionOrderController::class)
            ->parameters(['production-orders' => 'productionOrder']);
        Route::post('production-orders/{productionOrder}/start', [ProductionOrderController::class, 'start']);
        Route::post('production-orders/{productionOrder}/complete', [ProductionOrderController::class, 'complete']);
    });

    // crm (leads + opportunities pipeline)
    Route::prefix('v1/crm')->group(function () {
        Route::apiResource('leads', LeadController::class);
        Route::post('leads/{lead}/convert', [LeadController::class, 'convert']);

        Route::apiResource('opportunities', OpportunityController::class);
        Route::post('opportunities/{opportunity}/move-stage', [OpportunityController::class, 'moveStage']);
    });

    // payroll (دورات رواتب + قسائم، مرتبطة بـ hr/employees)
    Route::prefix('v1/payroll')->group(function () {
        Route::apiResource('runs', PayrollRunController::class)
            ->parameters(['runs' => 'run'])
            ->except(['update']);
        Route::post('runs/{run}/post', [PayrollRunController::class, 'post'])
            ->middleware('role:admin,accountant');
    });

    // helpdesk (تذاكر دعم فني / عملاء)
    Route::prefix('v1/helpdesk')->group(function () {
        Route::apiResource('tickets', TicketController::class);
        Route::post('tickets/{ticket}/resolve', [TicketController::class, 'resolve']);
        Route::post('tickets/{ticket}/close', [TicketController::class, 'close']);
    });

    // banking (حسابات بنكية + حركات + تسوية)
    Route::prefix('v1/banking')->group(function () {
        Route::apiResource('accounts', BankAccountController::class)
            ->parameters(['accounts' => 'bankAccount']);

        Route::apiResource('transactions', BankTransactionController::class)
            ->parameters(['transactions' => 'bankTransaction'])
            ->only(['index', 'store', 'show', 'destroy']);
        Route::post('transactions/{bankTransaction}/reconcile', [BankTransactionController::class, 'reconcile']);
    });

    // budgeting (ميزانيات مقارنة تلقائيًا بالفعلي من دفتر الأستاذ)
    Route::prefix('v1/budgeting')->group(function () {
        Route::apiResource('budgets', BudgetController::class);
    });

    // quality (تفتيشات جودة على المشاريع)
    Route::prefix('v1/quality')->group(function () {
        Route::apiResource('inspections', QualityInspectionController::class)
            ->parameters(['inspections' => 'qualityInspection']);
    });

    // recruitment (وظائف شاغرة + متقدمين)
    Route::prefix('v1/recruitment')->group(function () {
        Route::apiResource('job-openings', JobOpeningController::class)
            ->parameters(['job-openings' => 'jobOpening']);

        Route::apiResource('candidates', CandidateController::class);
        Route::post('candidates/{candidate}/hire', [CandidateController::class, 'hire']);
    });

    // document-management (مستندات مرتبطة بأي كيان عبر علاقة polymorphic)
    Route::prefix('v1/document-management')->group(function () {
        Route::get('documents/{document}/download', [DocumentController::class, 'download']);
        Route::apiResource('documents', DocumentController::class);
    });

    // core/users — إدارة المستخدمين (والقايمة اللي بتغذّي حقول "المسؤول" في الشاشات)
    Route::prefix('v1/core')->group(function () {
        Route::get('users', [UserController::class, 'index']);
        Route::get('users/{user}', [UserController::class, 'show']);

        Route::post('users', [UserController::class, 'store'])->middleware('role:admin');
        Route::put('users/{user}', [UserController::class, 'update'])->middleware('role:admin');
        Route::patch('users/{user}', [UserController::class, 'update'])->middleware('role:admin');
        Route::delete('users/{user}', [UserController::class, 'destroy'])->middleware('role:admin');

        // core/permissions — إدارة الأدوار والصلاحيات الدقيقة (فوق نظام الأدوار الأساسي)
        Route::get('permissions', [PermissionController::class, 'index']);
        Route::apiResource('roles', RoleController::class)->middleware('permission:core.roles.manage');
        Route::post('users/{user}/roles', [RoleController::class, 'syncUserRoles'])
            ->middleware('permission:core.roles.manage');
    });

    // modules/hotel — نظام فندقي كامل (غرف، حجوزات، housekeeping، POS، channel manager)
    Route::prefix('v1/hotel')->group(function () {
        Route::apiResource('room-types', RoomTypeController::class);

        Route::get('rooms/availability', [RoomController::class, 'availability']);
        Route::apiResource('rooms', RoomController::class);

        Route::apiResource('guests', GuestController::class);

        Route::post('reservations/{reservation}/check-in', [ReservationController::class, 'checkIn']);
        Route::post('reservations/{reservation}/check-out', [ReservationController::class, 'checkOut']);
        Route::apiResource('reservations', ReservationController::class);

        Route::apiResource('housekeeping-tasks', HousekeepingTaskController::class)->except(['show']);

        Route::apiResource('pos-outlets', PosOutletController::class);
        Route::apiResource('pos-products', PosProductController::class)->except(['show']);

        Route::post('pos-orders/{posOrder}/mark-paid', [PosOrderController::class, 'markPaid']);
        Route::apiResource('pos-orders', PosOrderController::class)->except(['update']);

        Route::post('channels/{channel}/sync', [ChannelController::class, 'sync']);
        Route::apiResource('channels', ChannelController::class)->except(['show']);
    });

    // modules/fleet — الأسطول والنقل (مركبات، سائقين، رحلات، صيانة، وقود)
    Route::prefix('v1/fleet')->group(function () {
        Route::apiResource('drivers', FleetDriverController::class);
        Route::apiResource('vehicles', FleetVehicleController::class);

        Route::post('trips/{trip}/start', [TripController::class, 'start']);
        Route::post('trips/{trip}/complete', [TripController::class, 'complete']);
        Route::apiResource('trips', TripController::class);

        Route::apiResource('maintenance-records', MaintenanceRecordController::class)->except(['show']);
        Route::apiResource('fuel-logs', FuelLogController::class)->except(['show']);

        Route::post('violations/{violation}/pay', [FleetViolationController::class, 'pay']);
        Route::apiResource('violations', FleetViolationController::class)->except(['show']);
    });

    // modules/retail — نقطة البيع (شيفتات كاشير، بيع، فاتورة وقيد تلقائي)
    Route::prefix('v1/retail')->group(function () {
        Route::post('register-sessions/{registerSession}/close', [RegisterSessionController::class, 'close']);
        Route::apiResource('register-sessions', RegisterSessionController::class)->except(['update', 'destroy']);

        Route::apiResource('sales', PosSaleController::class)->only(['index', 'store', 'show']);
    });
});

// Future modules register their own route groups here, e.g.:
// Route::prefix('v1/quality')->group(base_path('../../modules/quality/routes.php'));

// ── Settings Module ──────────────────────────────────────────
require base_path('routes/settings.php');

// ── Labor Market Module (سوق العمل) ─────────────────────────
require base_path('routes/labor_market.php');

// ── Platform Modules ─────────────────────────────────────────
require base_path('routes/platform_modules.php');

// ── Additional Available Modules ────────────────────────────
require base_path('routes/additional_modules.php');

// ── AI Layer ─────────────────────────────────────────────────
require base_path('routes/ai.php');

// ── Multi-tenant SaaS / Client Configuration ─────────────────
require base_path('routes/tenants.php');
