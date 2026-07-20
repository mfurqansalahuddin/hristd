<?php

return [

    'paths' => ['api/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'capacitor://localhost', // iOS Capacitor webview
        'http://localhost', // Android Capacitor webview, androidScheme: 'http'
        'https://localhost', // Android Capacitor webview, default androidScheme: 'https'
    ],

    'allowed_origins_patterns' => [
        '#^http://localhost:\d+$#', // Vite dev server (port varies if 5173 is taken)
        '#^http://192\.168\.\d{1,3}\.\d{1,3}(:\d+)?$#', // LAN IP, for on-device testing
    ],

    'allowed_headers' => ['Authorization', 'Content-Type', 'Accept', 'X-Requested-With'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
