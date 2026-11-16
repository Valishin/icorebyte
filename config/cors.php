<?php

return [

    /*
    |--------------------------------------------------------------------------
    | CORS — Rutas afectadas
    |--------------------------------------------------------------------------
    | Solo los endpoints de la API de chat necesitan cabeceras CORS.
    | Restringir 'allowed_origins' a APP_URL impide que scripts de terceros
    | llamen a la API desde otro origen.
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],

    // Único origen permitido: el dominio propio de la app.
    // En tests, APP_URL = 'http://localhost'. En producción, 'https://icorebyte.com'.
    'allowed_origins' => [env('APP_URL', 'http://localhost')],

    'allowed_origins_patterns' => [],

    'allowed_headers' => [
        'Content-Type',
        'Accept',
        'X-Requested-With',
        'X-Chat-Visitor-Token',
        'X-Telegram-Bot-Api-Secret-Token',
        'X-Diagnose-Token',
    ],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
