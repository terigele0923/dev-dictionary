<?php

declare(strict_types=1);

namespace App\Helpers;

final class Auth
{
    public static function user(): ?array
    {
        return Session::get('auth_user');
    }

    public static function id(): ?int
    {
        $user = self::user();
        return $user ? (int) $user['user_id'] : null;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function login(array $user): void
    {
        Session::regenerate();
        Session::put('auth_user', [
            'user_id' => (int) $user['user_id'],
            'login_id' => $user['login_id'],
            'user_name' => $user['user_name'],
            'role' => $user['role'],
        ]);
    }

    public static function logout(): void
    {
        Session::destroy();
    }
}
