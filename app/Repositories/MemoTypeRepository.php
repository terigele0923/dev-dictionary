<?php

declare(strict_types=1);

namespace App\Repositories;

final class MemoTypeRepository extends BaseRepository
{
    public function all(): array
    {
        return $this->pdo()->query('SELECT * FROM memo_types ORDER BY is_active DESC, type_name ASC')->fetchAll() ?: [];
    }

    public function allActive(): array
    {
        return $this->pdo()->query('SELECT * FROM memo_types WHERE is_active = 1 ORDER BY type_name ASC')->fetchAll() ?: [];
    }

    public function find(int $memoTypeId): ?array
    {
        $stmt = $this->pdo()->prepare('SELECT * FROM memo_types WHERE memo_type_id = :memo_type_id LIMIT 1');
        $stmt->execute(['memo_type_id' => $memoTypeId]);
        return $stmt->fetch() ?: null;
    }

    public function findByKey(string $typeKey): ?array
    {
        $stmt = $this->pdo()->prepare('SELECT * FROM memo_types WHERE type_key = :type_key LIMIT 1');
        $stmt->execute(['type_key' => $typeKey]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo()->prepare('INSERT INTO memo_types (type_name, type_key, description, display_mode, input_mode, is_active, created_at, updated_at) VALUES (:type_name, :type_key, :description, :display_mode, :input_mode, 1, :created_at, :updated_at)');
        $stmt->execute($data);
        return (int) $this->pdo()->lastInsertId();
    }

    public function update(int $memoTypeId, array $data): void
    {
        $stmt = $this->pdo()->prepare('UPDATE memo_types SET type_name = :type_name, type_key = :type_key, description = :description, display_mode = :display_mode, input_mode = :input_mode, updated_at = :updated_at WHERE memo_type_id = :memo_type_id');
        $stmt->execute([
            'type_name' => $data['type_name'],
            'type_key' => $data['type_key'],
            'description' => $data['description'],
            'display_mode' => $data['display_mode'],
            'input_mode' => $data['input_mode'],
            'updated_at' => $data['updated_at'],
            'memo_type_id' => $memoTypeId,
        ]);
    }

    public function delete(int $memoTypeId): void
    {
        $stmt = $this->pdo()->prepare('DELETE FROM memo_types WHERE memo_type_id = :memo_type_id');
        $stmt->execute(['memo_type_id' => $memoTypeId]);
    }

    public function countEntries(int $memoTypeId): int
    {
        $stmt = $this->pdo()->prepare('SELECT COUNT(*) FROM dictionary_entries WHERE memo_type_id = :memo_type_id AND deleted_at IS NULL');
        $stmt->execute(['memo_type_id' => $memoTypeId]);
        return (int) $stmt->fetchColumn();
    }
}
