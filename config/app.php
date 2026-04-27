<?php

declare(strict_types=1);

return [
    'name' => env('APP_NAME', 'Dev Dictionary'),
    'env' => env('APP_ENV', 'production'),
    'debug' => env('APP_DEBUG', 'false') === 'true',
    'url' => env('APP_URL', 'http://dictionary.local'),
    'key' => env('APP_KEY', 'change-me'),
    'session_name' => env('SESSION_NAME', 'dev_dictionary_session'),
];
