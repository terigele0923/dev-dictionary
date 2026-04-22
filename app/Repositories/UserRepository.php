<?php

declare(strict_types=1);

namespace App\Repositories;

final class UserRepository extends BaseRepository
{
    public function findByLoginId(string $loginId): ?array
    {
        $sql = 'SELECT * FROM users WHERE login_id = :login_id AND is_active = 1 LIMIT 1';
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute(['login_id' => $loginId]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): int
    {
        $sql = 'INSERT INTO users (login_id, user_name, email, password_hash, role, is_active, created_at, updated_at)
                VALUES (:login_id, :user_name, :email, :password_hash, :role, 1, :created_at, :updated_at)';
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($data);
        return (int) $this->pdo()->lastInsertId();
    }
}
