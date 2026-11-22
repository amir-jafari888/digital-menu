<?php
declare(strict_types=1);

use App\Core\Env;

/**
 * Application configuration.
 *
 * Every value can be overridden with an environment variable (see .env.example).
 * Defaults target a local XAMPP installation.
 */
return [
    'app' => [
        'env'   => Env::get('APP_ENV', 'local'),
        'debug' => filter_var(Env::get('APP_DEBUG', 'false'), FILTER_VALIDATE_BOOLEAN),
        'url'   => rtrim((string) Env::get('APP_URL', ''), '/'),
        'key'   => (string) Env::get('APP_KEY', ''),
    ],
    'db' => [
        'host'    => Env::get('DB_HOST', '127.0.0.1'),
        'port'    => (int) Env::get('DB_PORT', '3306'),
        'name'    => Env::get('DB_NAME', 'digital_menu'),
        'user'    => Env::get('DB_USER', 'root'),
        'pass'    => Env::get('DB_PASS', ''),
        'charset' => 'utf8mb4',
    ],
    'session' => [
        'name'     => Env::get('SESSION_NAME', 'MONOCHISESS'),
        'idle'     => (int) Env::get('SESSION_IDLE', '1800'),
        'absolute' => (int) Env::get('SESSION_ABSOLUTE', '43200'),
    ],
    'uploads' => [
        'max_bytes' => 2 * 1024 * 1024,
        'mimes'     => ['image/jpeg', 'image/png', 'image/webp'],
        'ext'       => ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'],
        'max_pixels' => 12_000_000,
    ],
    'security' => [
        'trusted_proxies' => array_values(array_filter(array_map(
            'trim',
            explode(',', (string) Env::get('TRUSTED_PROXIES', ''))
        ))),
    ],
];
