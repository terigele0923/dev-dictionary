<?php

declare(strict_types=1);

require __DIR__ . '/helpers.php';

spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    $baseDir = dirname(__DIR__) . '/app/';

    if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (is_file($file)) {
        require $file;
    }
});

use App\Helpers\Env;
use App\Helpers\Logger;
use App\Helpers\Session;

$envPath = dirname(__DIR__) . '/.env';
if (!is_file($envPath) && is_file(dirname(__DIR__) . '/.env.example')) {
    $envPath = dirname(__DIR__) . '/.env.example';
}

Env::load($envPath);
Session::start();

date_default_timezone_set('Asia/Tokyo');

set_exception_handler(function (Throwable $e): void {
    Logger::error('Unhandled exception', [
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
    ]);

    http_response_code(500);
    $view = dirname(__DIR__) . '/app/Views/errors/500.php';
    if (is_file($view)) {
        require $view;
        return;
    }
    echo 'Internal Server Error';
});

set_error_handler(function (int $severity, string $message, string $file, int $line): bool {
    if (!(error_reporting() & $severity)) {
        return false;
    }

    throw new ErrorException($message, 0, $severity, $file, $line);
});
