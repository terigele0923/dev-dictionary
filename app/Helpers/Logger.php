<?php

declare(strict_types=1);

namespace App\Helpers;

final class Logger
{
    public static function error(string $message, array $context = []): void
    {
        $path = dirname(__DIR__, 2) . '/storage/logs/app.log';
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $line = sprintf("[%s] ERROR %s %s\n", date('Y-m-d H:i:s'), $message, json_encode($context, JSON_UNESCAPED_UNICODE));
        @file_put_contents($path, $line, FILE_APPEND);
    }
}
