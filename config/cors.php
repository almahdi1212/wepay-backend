<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | هذه الإعدادات تسمح للتطبيقات الأمامية (مثل Vite أو Vercel)
    | بالتواصل مع واجهة Laravel API بأمان.
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://localhost:5173', // للواجهة المحلية
        'http://127.0.0.1:5173',
        'https://we-pay-zeta.vercel.app', // استبدل هذا برابط موقعك على Vercel لاحقاً
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
