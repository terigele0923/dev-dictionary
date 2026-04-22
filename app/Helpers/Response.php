<?php

declare(strict_types=1);

namespace App\Helpers;

final class Response
{
    public static function redirect(string $path): never
    {
        header('Location: ' . $path);
        exit;
    }

    public static function abort(int $statusCode = 404): never
    {
        http_response_code($statusCode);
        $view = dirname(__DIR__) . '/Views/errors/' . $statusCode . '.php';
        if (is_file($view)) {
            require $view;
        } else {
            echo $statusCode;
        }
        exit;
    }
}
