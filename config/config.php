<?php
/**
 * Clinic configuration.
 * On shared hosting, edit the four database values below and nothing else.
 */

return [
    'app' => [
        'name'     => 'Aesthetica',
        'base_url' => rtrim(getenv('APP_BASE_URL') ?: '', '/'),
        'debug'    => filter_var(getenv('APP_DEBUG') ?: 'false', FILTER_VALIDATE_BOOL),
        'timezone' => 'Asia/Beirut',
    ],

    'db' => [
        'host'     => getenv('DB_HOST') ?: '127.0.0.1',
        'name'     => getenv('DB_NAME') ?: 'aesthetica',
        'user'     => getenv('DB_USER') ?: 'root',
        'pass'     => getenv('DB_PASS') ?: '',
        'charset'  => 'utf8mb4',
    ],

    'uploads' => [
        'photos' => __DIR__ . '/../public/uploads/photos',
        'max_mb' => 8,
    ],
];
