<?php

/**
 * ═══════════════════════════════════════════════════════════════
 *  htdocs/setup.php — تشغيل المايجريشن من المتصفح (استخدام مؤقت)
 * ═══════════════════════════════════════════════════════════════
 *
 *  الاستضافة المشتركة مفيهاش SSH، يعني `php artisan migrate` مش ممكن.
 *  الملف ده بيشغّل نفس الأوامر عبر المتصفح، مرة واحدة، بتوكن سرّي.
 *
 *  الاستخدام:
 *    1) حط `INSTALL_TOKEN=<نص طويل عشوائي>` في erp-app/.env
 *    2) افتح: https://yourdomain/setup.php?token=<نفس النص>&action=migrate
 *    3) **امسح الملف ده من السيرفر بعد ما تخلص.**
 *
 *  ⚠️ الملف ده بيقدر يعدّل قاعدة البيانات ويمسحها. سيبه على السيرفر
 *     أطول من اللازم = خطر أمني حقيقي. امسحه.
 */

$app_path = __DIR__.'/erp-app';

require $app_path.'/vendor/autoload.php';

$app = require_once $app_path.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

header('Content-Type: text/plain; charset=utf-8');

// ── التحقق من التوكن ──────────────────────────────────────────
$expected = env('INSTALL_TOKEN');
$given = $_GET['token'] ?? '';

if (! $expected || strlen($expected) < 16) {
    http_response_code(500);
    exit("INSTALL_TOKEN مش محدد في .env أو أقصر من 16 حرف.\nحدده الأول بنص عشوائي طويل.\n");
}

// hash_equals عشان المقارنة تاخد نفس الوقت مهما كان الفرق (منع timing attack)
if (! hash_equals($expected, $given)) {
    http_response_code(403);
    exit("توكن غير صحيح.\n");
}

// ── تنفيذ الأمر ───────────────────────────────────────────────
$actions = [
    'migrate' => ['migrate', ['--force' => true]],
    'seed' => ['db:seed', ['--force' => true]],
    'fresh' => ['migrate:fresh', ['--force' => true, '--seed' => true]],
    'key' => ['key:generate', ['--force' => true, '--show' => true]],
    'optimize' => ['optimize', []],
    'clear' => ['optimize:clear', []],
    'status' => ['migrate:status', []],
];

$action = $_GET['action'] ?? 'status';

if (! isset($actions[$action])) {
    exit("action غير معروف.\nالمتاح: ".implode(', ', array_keys($actions))."\n");
}

// `fresh` بيمسح كل الجداول — محتاج تأكيد صريح زيادة
if ($action === 'fresh' && ($_GET['confirm'] ?? '') !== 'yes-delete-everything') {
    exit("الأمر ده بيمسح كل البيانات.\nلو متأكد ضيف: &confirm=yes-delete-everything\n");
}

[$command, $parameters] = $actions[$action];

echo "▶ php artisan {$command}\n";
echo str_repeat('─', 50)."\n";

try {
    Illuminate\Support\Facades\Artisan::call($command, $parameters);
    echo Illuminate\Support\Facades\Artisan::output();

    echo str_repeat('─', 50)."\n";
    echo "✓ تم.\n\n";
    echo "لما تخلص كل الخطوات: امسح setup.php من السيرفر.\n";
} catch (Throwable $e) {
    http_response_code(500);
    echo "✗ فشل: ".$e->getMessage()."\n\n";
    echo $e->getTraceAsString()."\n";
}
