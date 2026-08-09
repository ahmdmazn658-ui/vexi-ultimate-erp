<?php

return [
    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    // ZATCA e-invoicing (Phase 2 — يتم استخدامه لاحقاً في integrations/zatca)
    'zatca' => [
        'environment' => env('ZATCA_ENVIRONMENT', 'sandbox'),
        'csid' => env('ZATCA_CSID'),
        'api_base_url' => env('ZATCA_API_BASE_URL'),
    ],
];
