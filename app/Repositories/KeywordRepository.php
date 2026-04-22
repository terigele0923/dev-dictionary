<?php

declare(strict_types=1);

namespace App\Repositories;

final class KeywordRepository extends BaseRepository
{
    public function findByEntryId(int $entryId): array
    {
        $stmt = $this->pdo()->prepare('SELECT * FROM dictionary_entry_keywords WHERE entry_id = :entry_id ORDER BY sort_order ASC, keyword_id ASC');
        $stmt->execute(['entry_id' => $entryId]);
        return $stmt->fetchAll() ?: [];
    }

    public function replaceForEntry(int $entryId, array $keywords, int $userId): void
    {
        $delete = $this->pdo()->prepare('DELETE FROM dictionary_entry_keywords WHERE entry_id = :entry_id');
        $delete->execute(['entry_id' => $entryId]);

        $insert = $this->pdo()->prepare('INSERT INTO dictionary_entry_keywords (entry_id, keyword, sort_order, created_at, created_by) VALUES (:entry_id, :keyword, :sort_order, :created_at, :created_by)');
        foreach (array_values($keywords) as $index => $keyword) {
            $insert->execute([
                'entry_id' => $entryId,
                'keyword' => $keyword,
                'sort_order' => $index + 1,
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => $userId,
            ]);
        }
    }

    public function keywordsForSnapshot(int $entryId): string
    {
        $rows = $this->findByEntryId($entryId);
        $words = array_map(static fn(array $row): string => $row['keyword'], $rows);
        return implode(', ', $words);
    }
}
