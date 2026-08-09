<?php

use Illuminate\Support\Facades\Route;

/**
 * تقديم واجهة React من نفس الدومين.
 *
 * لما الواجهة والـ API يبقوا على دومين واحد (الوضع الافتراضي على الاستضافات
 * المشتركة زي InfinityFree)، بننسخ ناتج `npm run build` جوه `public/`. الملفات
 * الثابتة أباتشي بيقدّمها لوحده، وأي مسار تاني بيوصل هنا فبنرجّع `index.html`
 * عشان React Router يشتغل على الروابط المباشرة (deep links).
 *
 * لو الواجهة منشورة لوحدها (Render Static Site مثلاً)، الملف مش هيكون موجود
 * والراوت بيرجّع رد الـ API العادي.
 */
Route::get('/{path?}', function (?string $path = null) {
    $spa = public_path('index.html');

    if (file_exists($spa)) {
        return response()->file($spa);
    }

    return response()->json([
        'message' => 'ERP System API',
        'docs' => '/api/v1/ping',
    ]);
})->where('path', '(?!api/|up$|storage/).*');
