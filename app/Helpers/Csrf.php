<?php

declare(strict_types=1);

namespace App\Helpers;

final class Csrf
{
    public static function token(): string
    {
        $token = Session::get('_csrf_token');
        if (!is_string($token) || $token === '') {
            $token = bin2hex(random_bytes(32));
            Session::put('_csrf_token', $token);
        }
        return $token;
    }

    public static function verify(?string $token): bool
    {
        $stored = Session::get('_csrf_token');
        return is_string($stored) && is_string($token) && hash_equals($stored, $token);
    }
}
