<?php

declare(strict_types=1);

namespace App\Repositories;

final class MemoTypeFieldRepository extends BaseRepository
{
    public function findByTypeId(int $memoTypeId): array
    {
        $stmt = $this->pdo()->prepare('
            SELECT
                mtf.memo_type_field_id,
                mtf.memo_type_id,
                mtf.field_id,
                mtf.is_required,
                mtf.sort_order,
                mtf.label_override,
                mf.field_name,
                mf.field_key,
                mf.input_type,
                mf.default_required
            FROM memo_type_fields mtf
            INNER JOIN memo_fields mf ON mf.field_id = mtf.field_id
            WHERE mtf.memo_type_id = :memo_type_id
            ORDER BY mtf.sort_order ASC, mtf.memo_type_field_id ASC
        ');
        $stmt->execute(['memo_type_id' => $memoTypeId]);
        return $stmt->fetchAll() ?: [];
    }

    public function replaceForType(int $memoTypeId, array $rows): void
    {
        $delete = $this->pdo()->prepare('DELETE FROM memo_type_fields WHERE memo_type_id = :memo_type_id');
        $delete->execute(['memo_type_id' => $memoTypeId]);

        $insert = $this->pdo()->prepare('
            INSERT INTO memo_type_fields (memo_type_id, field_id, is_required, sort_order, label_override, created_at, updated_at)
            VALUES (:memo_type_id, :field_id, :is_required, :sort_order, :label_override, :created_at, :updated_at)
        ');
        foreach ($rows as $row) {
            $insert->execute($row + ['memo_type_id' => $memoTypeId]);
        }
    }

    public function deleteForType(int $memoTypeId): void
    {
        $delete = $this->pdo()->prepare('DELETE FROM memo_type_fields WHERE memo_type_id = :memo_type_id');
        $delete->execute(['memo_type_id' => $memoTypeId]);
    }
}
