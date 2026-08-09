<?php

/**
 * إعدادات CORS.
 *
 * في التطوير الواجهة بتشتغل عبر proxy في vite.config.ts فالطلبات same-origin
 * ومفيش CORS أصلاً. لكن في الإنتاج لما الواجهة تبقى على دومين مختلف عن الـ API،
 * لازم الدومين ده يبقى مسموح هنا وإلا المتصفح هيرفض كل الطلبات.
 *
 * المصادقة بالتوكن (Bearer) مش بالكوكيز، فـ supports_credentials مقفولة —
 * لو حوّلت لمصادقة SPA بالكوكيز، خليها true وحدد الدومينات صراحةً.
 */
return [

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => array_values(array_filter(
        explode(',', (string) env('CORS_ALLOWED_ORIGINS', 'http://localhost:5173'))
    )),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
