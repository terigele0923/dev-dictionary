<?php

declare(strict_types=1);

namespace App\Services;

use App\Helpers\Auth;
use App\Repositories\UserRepository;

final class AuthService
{
    public function __construct(private readonly UserRepository $users = new UserRepository())
    {
    }

    public function login(string $loginId, string $password): bool
    {
        $user = $this->users->findByLoginId($loginId);
        if (!$user || !password_verify($password, $user['password_hash'])) {
            return false;
        }

        Auth::login($user);
        return true;
    }

    public function register(array $input): void
    {
        if ($this->users->findByLoginId($input['login_id'])) {
            throw new \InvalidArgumentException('同じログインIDが既に存在します。');
        }

        $now = date('Y-m-d H:i:s');
        $this->users->create([
            'login_id' => $input['login_id'],
            'user_name' => $input['user_name'],
            'email' => $input['email'],
            'password_hash' => password_hash($input['password'], PASSWORD_DEFAULT),
            'role' => 'general',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}
