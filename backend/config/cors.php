<?php

$localOrigins = 'http://localhost:5173,http://127.0.0.1:5173,http://localhost:4173,http://127.0.0.1:4173';
$origins = env('FRONTEND_ORIGINS', env('APP_ENV') === 'local' ? $localOrigins : '');

return [
    'paths' => ['api/*'],
    'allowed_methods' => ['GET', 'HEAD', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
    'allowed_origins' => array_values(array_filter(
        array_map('trim', explode(',', (string) $origins)),
        fn (string $origin): bool => $origin !== '' && ! str_contains($origin, '*'),
    )),
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['Accept', 'Content-Type'],
    'exposed_headers' => [],
    'max_age' => 600,
    'supports_credentials' => false,
];
