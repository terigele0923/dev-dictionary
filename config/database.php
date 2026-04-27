<?php

declare(strict_types=1);

return [
    'connection' => env('DB_CONNECTION', 'sqlite'),
    'sqlite' => [
        'database' => env('DB_DATABASE', dirname(__DIR__) . '/storage/data/database.sqlite'),
    ],
    'mysql' => [
        'host' => env('DB_HOST', '127.0.0.1'),
        'port' => env('DB_PORT', '3306'),
        'database' => env('DB_NAME', 'dev_dictionary'),
        'username' => env('DB_USER', 'terigele'),
        'password' => env('DB_PASS', '123456'),
        'charset' => 'utf8mb4',
    ],
];
