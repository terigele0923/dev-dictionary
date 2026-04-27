<?php

declare(strict_types=1);

namespace App\Repositories;

final class HistoryRepository extends BaseRepository
{
    public function createSnapshot(array $entry, array $fieldSnapshot, string $keywords, int $userId): void
    {
        $sql = 'INSERT INTO dictionary_entry_histories (
                    entry_id, version_no, category_id, title, slug, problem_summary, root_cause, check_points,
                    command_examples, solution_summary, caution_notes, status, priority_level, keyword_snapshot,
                    snapshot_created_at, snapshot_created_by, memo_type_id, field_snapshot
                ) VALUES (
                    :entry_id, :version_no, :category_id, :title, :slug, :problem_summary, :root_cause, :check_points,
                    :command_examples, :solution_summary, :caution_notes, :status, :priority_level, :keyword_snapshot,
                    :snapshot_created_at, :snapshot_created_by, :memo_type_id, :field_snapshot
                )';
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute([
            'entry_id' => $entry['entry_id'],
            'version_no' => $entry['version_no'],
            'category_id' => $entry['category_id'],
            'title' => $entry['title'],
            'slug' => $entry['slug'],
            'problem_summary' => $entry['problem_summary'],
            'root_cause' => $entry['root_cause'],
            'check_points' => $entry['check_points'],
            'command_examples' => $entry['command_examples'],
            'solution_summary' => $entry['solution_summary'],
            'caution_notes' => $entry['caution_notes'],
            'status' => $entry['status'],
            'priority_level' => $entry['priority_level'],
            'keyword_snapshot' => $keywords,
            'snapshot_created_at' => date('Y-m-d H:i:s'),
            'snapshot_created_by' => $userId,
            'memo_type_id' => $entry['memo_type_id'],
            'field_snapshot' => json_encode($fieldSnapshot, JSON_UNESCAPED_UNICODE),
        ]);
    }

    public function listByEntryId(int $entryId): array
    {
        $stmt = $this->pdo()->prepare('SELECT h.*, c.category_name, mt.type_name AS memo_type_name, mt.display_mode AS memo_type_display_mode, mt.input_mode AS input_mode FROM dictionary_entry_histories h LEFT JOIN categories c ON c.category_id = h.category_id LEFT JOIN memo_types mt ON mt.memo_type_id = h.memo_type_id WHERE h.entry_id = :entry_id ORDER BY h.version_no DESC');
        $stmt->execute(['entry_id' => $entryId]);
        return $stmt->fetchAll() ?: [];
    }

    public function findForOwner(int $historyId, int $userId): ?array
    {
        $sql = 'SELECT h.*, c.category_name, e.user_id, mt.type_name AS memo_type_name, mt.display_mode AS memo_type_display_mode, mt.input_mode AS input_mode
                FROM dictionary_entry_histories h
                INNER JOIN dictionary_entries e ON e.entry_id = h.entry_id
                LEFT JOIN categories c ON c.category_id = h.category_id
                LEFT JOIN memo_types mt ON mt.memo_type_id = h.memo_type_id
                WHERE h.history_id = :history_id AND e.user_id = :user_id LIMIT 1';
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute(['history_id' => $historyId, 'user_id' => $userId]);
        return $stmt->fetch() ?: null;
    }
}
