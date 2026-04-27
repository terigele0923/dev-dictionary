<?php

declare(strict_types=1);

namespace App\Repositories;

final class DictionaryEntryRepository extends BaseRepository
{
    public function paginateByUser(int $userId, array $filters): array
    {
        $sql = 'SELECT e.*, c.category_name
                FROM dictionary_entries e
                LEFT JOIN categories c ON c.category_id = e.category_id
                WHERE e.user_id = :user_id AND e.deleted_at IS NULL';
        $params = ['user_id' => $userId];

        if (!empty($filters['category_id'])) {
            $sql .= ' AND e.category_id = :category_id';
            $params['category_id'] = (int) $filters['category_id'];
        }
        if (!empty($filters['status'])) {
            $sql .= ' AND e.status = :status';
            $params['status'] = $filters['status'];
        }
        if (!empty($filters['title'])) {
            $sql .= ' AND e.title LIKE :title';
            $params['title'] = '%' . $filters['title'] . '%';
        }
        if (!empty($filters['keyword'])) {
            $sql .= ' AND EXISTS (SELECT 1 FROM dictionary_entry_keywords k WHERE k.entry_id = e.entry_id AND k.keyword LIKE :keyword)';
            $params['keyword'] = '%' . $filters['keyword'] . '%';
        }

        $sql .= ' ORDER BY c.category_name ASC, e.updated_at DESC, e.entry_id DESC';
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll() ?: [];
    }

    public function findForOwner(int $entryId, int $userId): ?array
    {
        $sql = 'SELECT e.*, c.category_name
                FROM dictionary_entries e
                LEFT JOIN categories c ON c.category_id = e.category_id
                WHERE e.entry_id = :entry_id AND e.user_id = :user_id AND e.deleted_at IS NULL LIMIT 1';
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute(['entry_id' => $entryId, 'user_id' => $userId]);
        return $stmt->fetch() ?: null;
    }

    public function existsSlug(int $userId, string $slug, ?int $ignoreEntryId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM dictionary_entries WHERE user_id = :user_id AND slug = :slug AND deleted_at IS NULL';
        $params = ['user_id' => $userId, 'slug' => $slug];
        if ($ignoreEntryId !== null) {
            $sql .= ' AND entry_id <> :entry_id';
            $params['entry_id'] = $ignoreEntryId;
        }
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function create(array $data): int
    {
        $sql = 'INSERT INTO dictionary_entries (
                    user_id, category_id, title, slug, problem_summary, root_cause, check_points, command_examples,
                    solution_summary, caution_notes, status, priority_level, version_no, published_at, created_at,
                    created_by, updated_at, updated_by
                ) VALUES (
                    :user_id, :category_id, :title, :slug, :problem_summary, :root_cause, :check_points, :command_examples,
                    :solution_summary, :caution_notes, :status, :priority_level, :version_no, :published_at, :created_at,
                    :created_by, :updated_at, :updated_by
                )';
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($data);
        return (int) $this->pdo()->lastInsertId();
    }

    public function update(int $entryId, int $userId, array $data): void
    {
        $sql = 'UPDATE dictionary_entries SET
                    category_id = :category_id,
                    title = :title,
                    slug = :slug,
                    problem_summary = :problem_summary,
                    root_cause = :root_cause,
                    check_points = :check_points,
                    command_examples = :command_examples,
                    solution_summary = :solution_summary,
                    caution_notes = :caution_notes,
                    status = :status,
                    priority_level = :priority_level,
                    version_no = :version_no,
                    published_at = :published_at,
                    updated_at = :updated_at,
                    updated_by = :updated_by
                WHERE entry_id = :entry_id AND user_id = :user_id AND deleted_at IS NULL';
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute([
            'category_id' => $data['category_id'],
            'title' => $data['title'],
            'slug' => $data['slug'],
            'problem_summary' => $data['problem_summary'],
            'root_cause' => $data['root_cause'],
            'check_points' => $data['check_points'],
            'command_examples' => $data['command_examples'],
            'solution_summary' => $data['solution_summary'],
            'caution_notes' => $data['caution_notes'],
            'status' => $data['status'],
            'priority_level' => $data['priority_level'],
            'version_no' => $data['version_no'],
            'published_at' => $data['published_at'],
            'updated_at' => $data['updated_at'],
            'updated_by' => $data['updated_by'],
            'entry_id' => $entryId,
            'user_id' => $userId,
        ]);
    }

    public function softDelete(int $entryId, int $userId): void
    {
        $stmt = $this->pdo()->prepare('UPDATE dictionary_entries SET deleted_at = :deleted_at, deleted_by = :deleted_by, updated_at = :updated_at, updated_by = :updated_by WHERE entry_id = :entry_id AND user_id = :user_id AND deleted_at IS NULL');
        $stmt->execute([
            'deleted_at' => date('Y-m-d H:i:s'),
            'deleted_by' => $userId,
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => $userId,
            'entry_id' => $entryId,
            'user_id' => $userId,
        ]);
    }
}
