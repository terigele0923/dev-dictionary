<?php

declare(strict_types=1);

namespace App\Repositories;

final class EntryFieldValueRepository extends BaseRepository
{
    public function findByEntryId(int $entryId): array
    {
        $stmt = $this->pdo()->prepare('
            SELECT
                v.value_id,
                v.entry_id,
                v.field_id,
                v.row_no,
                v.field_value,
                mf.field_name,
                mf.field_key,
                mf.input_type
            FROM dictionary_entry_field_values v
            INNER JOIN memo_fields mf ON mf.field_id = v.field_id
            WHERE v.entry_id = :entry_id
            ORDER BY v.value_id ASC
        ');
        $stmt->execute(['entry_id' => $entryId]);
        return $stmt->fetchAll() ?: [];
    }

    public function valuesByFieldId(int $entryId): array
    {
        $rows = $this->findByEntryId($entryId);
        $values = [];
        foreach ($rows as $row) {
            if ((int) ($row['row_no'] ?? 1) !== 1) {
                continue;
            }
            $values[(int) $row['field_id']] = (string) ($row['field_value'] ?? '');
        }
        return $values;
    }

    public function rowsByEntryId(int $entryId): array
    {
        $rows = $this->findByEntryId($entryId);
        $grouped = [];
        foreach ($rows as $row) {
            $rowNo = (int) ($row['row_no'] ?? 1);
            $grouped[$rowNo][(int) $row['field_id']] = (string) ($row['field_value'] ?? '');
        }
        ksort($grouped);
        return $grouped;
    }

    public function replaceForEntry(int $entryId, array $fieldRows): void
    {
        $delete = $this->pdo()->prepare('DELETE FROM dictionary_entry_field_values WHERE entry_id = :entry_id');
        $delete->execute(['entry_id' => $entryId]);

        $insert = $this->pdo()->prepare('
            INSERT INTO dictionary_entry_field_values (entry_id, field_id, row_no, field_value, created_at, updated_at)
            VALUES (:entry_id, :field_id, :row_no, :field_value, :created_at, :updated_at)
        ');
        $now = date('Y-m-d H:i:s');
        foreach ($fieldRows as $rowNo => $fieldValues) {
            foreach ($fieldValues as $fieldId => $fieldValue) {
                $text = trim((string) $fieldValue);
                if ($text === '') {
                    continue;
                }
                $insert->execute([
                    'entry_id' => $entryId,
                    'field_id' => (int) $fieldId,
                    'row_no' => (int) $rowNo,
                    'field_value' => $text,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }
}
