<?php

declare(strict_types=1);

namespace App\Helpers;

final class Flash
{
    public static function success(string $message): void
    {
        Session::put('_flash_success', $message);
    }

    public static function error(string $message): void
    {
        Session::put('_flash_error', $message);
    }

    public static function get(string $type): ?string
    {
        $key = '_flash_' . $type;
        $message = Session::get($key);
        Session::forget($key);
        return $message;
    }
}
