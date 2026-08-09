<?php

/**
 * ═══════════════════════════════════════════════════════════════
 *  htdocs/index.php — نقطة الدخول على الاستضافة المشتركة
 * ═══════════════════════════════════════════════════════════════
 *
 * الفرق عن `public/index.php` الأصلي: ملفات التطبيق مش فوق مجلد الويب،
 * لأن الاستضافة المشتركة بتخلي كل حاجة جوه `htdocs`. فبدل `__DIR__.'/..'`
 * بنشاور على `htdocs/erp-app/` — والمجلد ده محمي بـ `.htaccess` بيمنع
 * أي وصول مباشر ليه من المتصفح.
 *
 * لو غيّرت اسم المجلد، غيّر السطر اللي تحت بس.
 */

define('LARAVEL_START', microtime(true));

$app_path = __DIR__.'/erp-app';

if (! file_exists($app_path.'/vendor/autoload.php')) {
    http_response_code(500);
    exit('مجلد vendor مش موجود. ارفع مجلد erp-app كامل، أو راجع دليل النشر.');
}

if (file_exists($maintenance = $app_path.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

require $app_path.'/vendor/autoload.php';

$app = require_once $app_path.'/bootstrap/app.php';

$app->handleRequest(Illuminate\Http\Request::capture());
