<?php

declare(strict_types=1);

namespace App\Repositories;

final class MemoFieldRepository extends BaseRepository
{
    public function all(): array
    {
        return $this->pdo()->query('SELECT * FROM memo_fields ORDER BY is_active DESC, field_name ASC')->fetchAll() ?: [];
    }

    public function allActive(): array
    {
        return $this->pdo()->query('SELECT * FROM memo_fields WHERE is_active = 1 ORDER BY field_name ASC')->fetchAll() ?: [];
    }

    public function findByKey(string $fieldKey): ?array
    {
        $stmt = $this->pdo()->prepare('SELECT * FROM memo_fields WHERE field_key = :field_key LIMIT 1');
        $stmt->execute(['field_key' => $fieldKey]);
        return $stmt->fetch() ?: null;
    }

    public function find(int $fieldId): ?array
    {
        $stmt = $this->pdo()->prepare('SELECT * FROM memo_fields WHERE field_id = :field_id LIMIT 1');
        $stmt->execute(['field_id' => $fieldId]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo()->prepare('INSERT INTO memo_fields (field_name, field_key, input_type, default_required, is_active, created_at, updated_at) VALUES (:field_name, :field_key, :input_type, :default_required, 1, :created_at, :updated_at)');
        $stmt->execute($data);
        return (int) $this->pdo()->lastInsertId();
    }

    public function update(int $fieldId, array $data): void
    {
        $stmt = $this->pdo()->prepare('UPDATE memo_fields SET field_name = :field_name, field_key = :field_key, input_type = :input_type, default_required = :default_required, updated_at = :updated_at WHERE field_id = :field_id');
        $stmt->execute([
            'field_name' => $data['field_name'],
            'field_key' => $data['field_key'],
            'input_type' => $data['input_type'],
            'default_required' => $data['default_required'],
            'updated_at' => $data['updated_at'],
            'field_id' => $fieldId,
        ]);
    }

    public function delete(int $fieldId): void
    {
        $stmt = $this->pdo()->prepare('DELETE FROM memo_fields WHERE field_id = :field_id');
        $stmt->execute(['field_id' => $fieldId]);
    }

    public function countTypeLinks(int $fieldId): int
    {
        $stmt = $this->pdo()->prepare('SELECT COUNT(*) FROM memo_type_fields WHERE field_id = :field_id');
        $stmt->execute(['field_id' => $fieldId]);
        return (int) $stmt->fetchColumn();
    }

    public function countValues(int $fieldId): int
    {
        $stmt = $this->pdo()->prepare('SELECT COUNT(*) FROM dictionary_entry_field_values WHERE field_id = :field_id');
        $stmt->execute(['field_id' => $fieldId]);
        return (int) $stmt->fetchColumn();
    }
}
