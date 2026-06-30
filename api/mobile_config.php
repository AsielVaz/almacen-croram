<?php
return [
    'token' => getenv('CRORAM_MOBILE_API_TOKEN') ?: 'croram-mobile-dev-2026-cambiar',
    'allowed_origins' => [
        'https://almacen.grupocroram.com',
        'http://localhost:19006',
        'http://localhost:8081',
        'http://127.0.0.1:19006',
        'http://127.0.0.1:8081',
    ],
];
