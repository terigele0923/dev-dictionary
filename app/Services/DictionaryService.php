<?php

declare(strict_types=1);

namespace App\Services;

use App\Helpers\Database;
use App\Repositories\DictionaryEntryRepository;
use App\Repositories\HistoryRepository;
use App\Repositories\KeywordRepository;

final class DictionaryService
{
    public function __construct(
        private readonly DictionaryEntryRepository $entries = new DictionaryEntryRepository(),
        private readonly KeywordRepository $keywords = new KeywordRepository(),
        private readonly HistoryRepository $histories = new HistoryRepository()
    ) {
    }

    public function list(int $userId, array $filters): array
    {
        return $this->entries->paginateByUser($userId, $filters);
    }

    public function detail(int $entryId, int $userId): array
    {
        $entry = $this->entries->findForOwner($entryId, $userId);
        if (!$entry) {
            throw new \RuntimeException('対象データが見つかりません。');
        }
        $entry['keywords'] = $this->keywords->findByEntryId($entryId);
        return $entry;
    }

    public function histories(int $entryId, int $userId): array
    {
        $entry = $this->entries->findForOwner($entryId, $userId);
        if (!$entry) {
            throw new \RuntimeException('対象データが見つかりません。');
        }
        return $this->histories->listByEntryId($entryId);
    }

    public function historyDetail(int $historyId, int $userId): array
    {
        $history = $this->histories->findForOwner($historyId, $userId);
        if (!$history) {
            throw new \RuntimeException('履歴が見つかりません。');
        }
        return $history;
    }

    public function create(int $userId, array $input): int
    {
        $payload = $this->validate($input, $userId);
        $db = Database::connection();
        $db->beginTransaction();
        try {
            $entryId = $this->entries->create($payload['entry']);
            $this->keywords->replaceForEntry($entryId, $payload['keywords'], $userId);
            $entry = $this->entries->findForOwner($entryId, $userId);
            $entry['keywords'] = $this->keywords->findByEntryId($entryId);
            $this->histories->createSnapshot($entry, $this->keywords->keywordsForSnapshot($entryId), $userId);
            $db->commit();
            return $entryId;
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public function update(int $entryId, int $userId, array $input): void
    {
        $existing = $this->entries->findForOwner($entryId, $userId);
        if (!$existing) {
            throw new \RuntimeException('対象データが見つかりません。');
        }
        $payload = $this->validate($input, $userId, $entryId, (int) $existing['version_no'] + 1);
        $db = Database::connection();
        $db->beginTransaction();
        try {
            $this->entries->update($entryId, $userId, $payload['entry']);
            $this->keywords->replaceForEntry($entryId, $payload['keywords'], $userId);
            $entry = $this->entries->findForOwner($entryId, $userId);
            $this->histories->createSnapshot($entry, $this->keywords->keywordsForSnapshot($entryId), $userId);
            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public function delete(int $entryId, int $userId): void
    {
        $existing = $this->entries->findForOwner($entryId, $userId);
        if (!$existing) {
            throw new \RuntimeException('対象データが見つかりません。');
        }
        $this->entries->softDelete($entryId, $userId);
    }

    private function validate(array $input, int $userId, ?int $ignoreEntryId = null, int $versionNo = 1): array
    {
        $title = trim((string) ($input['title'] ?? ''));
        $categoryId = (int) ($input['category_id'] ?? 0);
        $slug = trim((string) ($input['slug'] ?? ''));
        $status = trim((string) ($input['status'] ?? 'draft'));
        $priority = (int) ($input['priority_level'] ?? 3);

        if ($categoryId <= 0) {
            throw new \InvalidArgumentException('カテゴリは必須です。');
        }
        if ($title === '' || mb_strlen($title) > 200) {
            throw new \InvalidArgumentException('タイトルは1〜200文字で入力してください。');
        }
        if ($slug === '') {
            $slug = $this->makeSlug($title);
        }
        if (mb_strlen($slug) > 200) {
            throw new \InvalidArgumentException('slugは200文字以内です。');
        }
        if (!in_array($status, ['draft', 'published', 'archived'], true)) {
            throw new \InvalidArgumentException('ステータスが不正です。');
        }
        if ($priority < 1 || $priority > 5) {
            throw new \InvalidArgumentException('優先度は1〜5で入力してください。');
        }
        if ($this->entries->existsSlug($userId, $slug, $ignoreEntryId)) {
            throw new \InvalidArgumentException('同じslugが既に存在します。');
        }

        $keywords = $this->normalizeKeywords((string) ($input['keywords'] ?? ''));
        $now = date('Y-m-d H:i:s');
        $publishedAt = $status === 'published' ? ($input['published_at'] ?? $now) : null;

        return [
            'entry' => [
                'user_id' => $userId,
                'category_id' => $categoryId,
                'title' => $title,
                'slug' => $slug,
                'problem_summary' => $this->nullable($input['problem_summary'] ?? null),
                'root_cause' => $this->nullable($input['root_cause'] ?? null),
                'check_points' => $this->nullable($input['check_points'] ?? null),
                'command_examples' => $this->nullable($input['command_examples'] ?? null),
                'solution_summary' => $this->nullable($input['solution_summary'] ?? null),
                'caution_notes' => $this->nullable($input['caution_notes'] ?? null),
                'status' => $status,
                'priority_level' => $priority,
                'version_no' => $versionNo,
                'published_at' => $publishedAt,
                'created_at' => $now,
                'created_by' => $userId,
                'updated_at' => $now,
                'updated_by' => $userId,
            ],
            'keywords' => $keywords,
        ];
    }

    private function normalizeKeywords(string $input): array
    {
        $parts = preg_split('/[,、\n]+/u', $input) ?: [];
        $clean = [];
        foreach ($parts as $part) {
            $word = trim($part);
            if ($word === '') {
                continue;
            }
            if (mb_strlen($word) > 100) {
                throw new \InvalidArgumentException('キーワードは100文字以内です。');
            }
            $clean[mb_strtolower($word)] = $word;
        }
        return array_values($clean);
    }

    private function nullable(mixed $value): ?string
    {
        $text = trim((string) $value);
        return $text === '' ? null : $text;
    }

    private function makeSlug(string $title): string
    {
        $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $title) ?? ''));
        $slug = trim($slug, '-');
        return $slug !== '' ? $slug : 'entry-' . bin2hex(random_bytes(4));
    }
}
