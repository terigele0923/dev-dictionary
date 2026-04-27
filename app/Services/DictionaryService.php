<?php

declare(strict_types=1);

namespace App\Services;

use App\Helpers\Database;
use App\Repositories\DictionaryEntryRepository;
use App\Repositories\EntryFieldValueRepository;
use App\Repositories\HistoryRepository;
use App\Repositories\KeywordRepository;

final class DictionaryService
{
    public function __construct(
        private readonly DictionaryEntryRepository $entries = new DictionaryEntryRepository(),
        private readonly KeywordRepository $keywords = new KeywordRepository(),
        private readonly HistoryRepository $histories = new HistoryRepository(),
        private readonly EntryFieldValueRepository $fieldValues = new EntryFieldValueRepository(),
        private readonly MemoTypeService $memoTypes = new MemoTypeService()
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
        $memoFields = $this->memoTypes->fieldsForType((int) $entry['memo_type_id']);
        $inputMode = (string) ($entry['input_mode'] ?? 'section');
        $entry['field_values'] = $this->buildFieldViewValues($memoFields, $this->fieldValues->valuesByFieldId($entryId));
        $entry['field_rows'] = $this->buildFieldRowViewValues($memoFields, $this->fieldValues->rowsByEntryId($entryId), $inputMode);
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
        $decodedSnapshot = $this->decodeFieldSnapshot($history['field_snapshot'] ?? null);
        $history['field_snapshot_mode'] = $decodedSnapshot['input_mode'] ?? 'section';
        $history['field_snapshots'] = $decodedSnapshot['rows'][1] ?? [];
        $history['field_snapshot_rows'] = $decodedSnapshot['rows'] ?? [];
        return $history;
    }

    public function create(int $userId, array $input): int
    {
        $payload = $this->validate($input, $userId);
        $db = Database::connection();
        $db->beginTransaction();
        try {
            $entryId = $this->entries->create($payload['entry']);
            $this->fieldValues->replaceForEntry($entryId, $payload['field_values']);
            $this->keywords->replaceForEntry($entryId, $payload['keywords'], $userId);
            $entry = $this->entries->findForOwner($entryId, $userId);
            $entry['keywords'] = $this->keywords->findByEntryId($entryId);
            $this->histories->createSnapshot($entry, $payload['field_snapshot'], $this->keywords->keywordsForSnapshot($entryId), $userId);
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
            $this->fieldValues->replaceForEntry($entryId, $payload['field_values']);
            $this->keywords->replaceForEntry($entryId, $payload['keywords'], $userId);
            $entry = $this->entries->findForOwner($entryId, $userId);
            $this->histories->createSnapshot($entry, $payload['field_snapshot'], $this->keywords->keywordsForSnapshot($entryId), $userId);
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
        $memoTypeId = (int) ($input['memo_type_id'] ?? 0);
        $categoryId = (int) ($input['category_id'] ?? 0);
        $status = trim((string) ($input['status'] ?? 'draft'));
        $priority = (int) ($input['priority_level'] ?? 3);
        $memoType = $this->memoTypes->find($memoTypeId);
        if (!$memoType || empty($memoType['is_active'])) {
            throw new \InvalidArgumentException('メモタイプを選択してください。');
        }
        $memoFields = $memoType['fields'] ?? [];
        $inputMode = (string) ($memoType['input_mode'] ?? 'section');

        if ($categoryId <= 0) {
            throw new \InvalidArgumentException('カテゴリは必須です。');
        }
        if ($title === '' || mb_strlen($title) > 200) {
            throw new \InvalidArgumentException('タイトルは1〜200文字で入力してください。');
        }
        $slug = $this->makeSlug($title);
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

        [$normalizedFieldRows, $fieldSnapshotRows] = $this->normalizeFieldRows($memoFields, $inputMode, $input);

        $keywords = $this->normalizeKeywords((string) ($input['keywords'] ?? ''));
        $now = date('Y-m-d H:i:s');
        $publishedAt = $status === 'published' ? ($input['published_at'] ?? $now) : null;

        return [
            'entry' => [
                'user_id' => $userId,
                'memo_type_id' => $memoTypeId,
                'category_id' => $categoryId,
                'title' => $title,
                'slug' => $slug,
                'problem_summary' => null,
                'root_cause' => null,
                'check_points' => null,
                'command_examples' => null,
                'solution_summary' => null,
                'caution_notes' => null,
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
            'field_values' => $normalizedFieldRows,
            'field_snapshot' => [
                'input_mode' => $inputMode,
                'rows' => $fieldSnapshotRows,
            ],
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

    private function buildFieldViewValues(array $fields, array $valuesByFieldId): array
    {
        $rows = [];
        foreach ($fields as $field) {
            $fieldId = (int) $field['field_id'];
            $rows[] = [
                'field_id' => $fieldId,
                'field_key' => $field['field_key'],
                'label' => $field['display_label'] ?? $field['field_name'],
                'input_type' => $field['input_type'],
                'value' => (string) ($valuesByFieldId[$fieldId] ?? ''),
            ];
        }
        return $rows;
    }

    private function buildFieldRowViewValues(array $fields, array $rowValuesByEntry, string $inputMode): array
    {
        if ($inputMode !== 'table_rows') {
            return [];
        }

        $rows = [];
        foreach ($rowValuesByEntry as $rowNo => $valuesByFieldId) {
            $row = ['row_no' => (int) $rowNo, 'columns' => []];
            foreach ($fields as $field) {
                $fieldId = (int) $field['field_id'];
                $row['columns'][] = [
                    'field_id' => $fieldId,
                    'field_key' => $field['field_key'],
                    'label' => $field['display_label'] ?? $field['field_name'],
                    'input_type' => $field['input_type'],
                    'value' => (string) ($valuesByFieldId[$fieldId] ?? ''),
                ];
            }
            $rows[] = $row;
        }

        return $rows !== [] ? $rows : [['row_no' => 1, 'columns' => array_map(fn(array $field): array => [
            'field_id' => (int) $field['field_id'],
            'field_key' => $field['field_key'],
            'label' => $field['display_label'] ?? $field['field_name'],
            'input_type' => $field['input_type'],
            'value' => '',
        ], $fields)]];
    }

    private function decodeFieldSnapshot(mixed $value): array
    {
        if (!is_string($value) || trim($value) === '') {
            return ['input_mode' => 'section', 'rows' => []];
        }
        $decoded = json_decode($value, true);
        if (!is_array($decoded)) {
            return ['input_mode' => 'section', 'rows' => []];
        }
        if (array_key_exists('rows', $decoded)) {
            return $decoded;
        }

        return ['input_mode' => 'section', 'rows' => [1 => $decoded]];
    }

    private function normalizeFieldRows(array $memoFields, string $inputMode, array $input): array
    {
        $rowsInput = $inputMode === 'table_rows'
            ? (array) ($input['field_rows'] ?? [])
            : [1 => (array) ($input['field_values'] ?? [])];

        $normalizedRows = [];
        $snapshotRows = [];
        foreach ($rowsInput as $rawRowNo => $fieldValues) {
            $rowNo = max(1, (int) $rawRowNo);
            $fieldValues = is_array($fieldValues) ? $fieldValues : [];
            $normalizedRow = [];
            $snapshotRow = [];
            $hasAnyValue = false;

            foreach ($memoFields as $field) {
                $fieldId = (int) $field['field_id'];
                $value = trim((string) ($fieldValues[$fieldId] ?? ''));
                $label = (string) ($field['display_label'] ?? $field['field_name']);
                if (!empty($field['required_flag']) && $value === '') {
                    throw new \InvalidArgumentException($label . 'は必須です。');
                }
                if ($value === '') {
                    continue;
                }
                $hasAnyValue = true;
                $normalizedRow[$fieldId] = $value;
                $snapshotRow[] = [
                    'field_id' => $fieldId,
                    'field_key' => $field['field_key'],
                    'label' => $label,
                    'input_type' => $field['input_type'],
                    'value' => $value,
                ];
            }

            if (!$hasAnyValue) {
                continue;
            }

            $normalizedRows[$rowNo] = $normalizedRow;
            $snapshotRows[$rowNo] = $snapshotRow;
        }

        if ($inputMode === 'table_rows' && $normalizedRows === []) {
            throw new \InvalidArgumentException('1行以上入力してください。');
        }

        return [$normalizedRows, $snapshotRows];
    }

    private function makeSlug(string $title): string
    {
        $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $title) ?? ''));
        $slug = trim($slug, '-');
        return $slug !== '' ? $slug : 'entry-' . bin2hex(random_bytes(4));
    }
}
