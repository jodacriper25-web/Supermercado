<?php
// Configuración básica
// Environment-aware configuration: allows CI to override DB settings with env vars
$env = function($k, $default=''){ $v = getenv($k); return $v !== false ? $v : $default; };

return [
    'db' => [
        'host' => $env('DB_HOST', '127.0.0.1'),
        'name' => $env('DB_NAME', 'supermercado_db'),
        'user' => $env('DB_USER', 'root'),
        'pass' => $env('DB_PASS', '')
    ],
    'app' => [
        'base_url' => $env('APP_BASE_URL', '/Supermercado'),
        'invoice_path' => __DIR__ . '/../../invoices'
    ],
    'mail' => [
        // Configuración de ejemplo. Ajusta en producción.
        'from' => $env('MAIL_FROM', 'no-reply@supermercado.local'),
        'smtp' => [
            'host' => $env('MAIL_HOST', 'smtp.example.com'),
            'port' => intval($env('MAIL_PORT', 587)),
            'username' => $env('MAIL_USER', 'user@example.com'),
            'password' => $env('MAIL_PASS', 'secret'),
            'secure' => $env('MAIL_SECURE', 'tls')
        ]
    ]
];
