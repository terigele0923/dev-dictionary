<?php

declare(strict_types=1);

namespace App\Services;

use App\Helpers\Database;
use App\Repositories\MemoFieldRepository;
use App\Repositories\MemoTypeFieldRepository;
use App\Repositories\MemoTypeRepository;

final class MemoTypeService
{
    public function __construct(
        private readonly MemoTypeRepository $types = new MemoTypeRepository(),
        private readonly MemoFieldRepository $fields = new MemoFieldRepository(),
        private readonly MemoTypeFieldRepository $typeFields = new MemoTypeFieldRepository()
    ) {
    }

    public function list(): array
    {
        $types = $this->types->all();
        foreach ($types as &$type) {
            $type['fields'] = $this->typeFields->findByTypeId((int) $type['memo_type_id']);
        }
        unset($type);
        return $types;
    }

    public function listActive(): array
    {
        return $this->types->allActive();
    }

    public function fieldsForType(?int $memoTypeId): array
    {
        if ($memoTypeId === null || $memoTypeId <= 0) {
            return [];
        }

        $fields = $this->typeFields->findByTypeId($memoTypeId);
        foreach ($fields as &$field) {
            $field['display_label'] = $field['label_override'] ?: $field['field_name'];
            $field['required_flag'] = !empty($field['is_required']) || !empty($field['default_required']);
        }
        unset($field);

        return $fields;
    }

    public function find(int $memoTypeId): ?array
    {
        $type = $this->types->find($memoTypeId);
        if (!$type) {
            return null;
        }
        $type['fields'] = $this->fieldsForType($memoTypeId);
        return $type;
    }

    public function create(array $input): void
    {
        [$data, $rows] = $this->normalizeTypeInput($input);

        $db = Database::connection();
        $db->beginTransaction();
        try {
            $memoTypeId = $this->types->create($data);
            $this->typeFields->replaceForType($memoTypeId, $rows);
            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public function update(int $memoTypeId, array $input): void
    {
        $existing = $this->types->find($memoTypeId);
        if (!$existing) {
            throw new \RuntimeException('対象のメモタイプが見つかりません。');
        }
        if (($existing['type_key'] ?? '') === 'standard') {
            throw new \RuntimeException('標準メモタイプは編集できません。');
        }

        [$data, $rows] = $this->normalizeTypeInput($input, $memoTypeId);

        $db = Database::connection();
        $db->beginTransaction();
        try {
            $this->types->update($memoTypeId, $data);
            $this->typeFields->replaceForType($memoTypeId, $rows);
            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public function delete(int $memoTypeId): void
    {
        $existing = $this->types->find($memoTypeId);
        if (!$existing) {
            throw new \RuntimeException('対象のメモタイプが見つかりません。');
        }
        if (($existing['type_key'] ?? '') === 'standard') {
            throw new \RuntimeException('標準メモタイプは削除できません。');
        }
        if ($this->types->countEntries($memoTypeId) > 0) {
            throw new \RuntimeException('使用中のメモタイプは削除できません。先に該当メモのタイプを変更してください。');
        }

        $db = Database::connection();
        $db->beginTransaction();
        try {
            $this->typeFields->deleteForType($memoTypeId);
            $this->types->delete($memoTypeId);
            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    private function normalizeTypeInput(array $input, ?int $ignoreMemoTypeId = null): array
    {
        $name = trim((string) ($input['type_name'] ?? ''));
        $key = trim((string) ($input['type_key'] ?? ''));
        $description = trim((string) ($input['description'] ?? ''));
        $displayMode = trim((string) ($input['display_mode'] ?? 'section'));
        $inputMode = trim((string) ($input['input_mode'] ?? 'section'));
        $selectedFieldIds = array_map('intval', (array) ($input['field_ids'] ?? []));
        $selectedFieldIds = array_values(array_unique(array_filter($selectedFieldIds, static fn(int $id): bool => $id > 0)));

        if ($name === '') {
            throw new \InvalidArgumentException('タイプ名は必須です。');
        }
        if ($key === '') {
            $key = $this->slugify($name);
        }
        if (!preg_match('/^[a-z][a-z0-9_]*$/', $key)) {
            throw new \InvalidArgumentException('タイプキーは英小文字・数字・アンダースコアで入力してください。');
        }
        if (!in_array($displayMode, ['section', 'table'], true)) {
            throw new \InvalidArgumentException('表示形式が不正です。');
        }
        if (!in_array($inputMode, ['section', 'table_rows'], true)) {
            throw new \InvalidArgumentException('入力形式が不正です。');
        }

        $byKey = $this->types->findByKey($key);
        if ($byKey && (int) $byKey['memo_type_id'] !== $ignoreMemoTypeId) {
            throw new \InvalidArgumentException('同じタイプキーが既に存在します。');
        }
        if ($selectedFieldIds === []) {
            throw new \InvalidArgumentException('項目を1つ以上選択してください。');
        }

        $allFields = $this->fields->allActive();
        $fieldMap = [];
        foreach ($allFields as $field) {
            $fieldMap[(int) $field['field_id']] = $field;
        }

        $rows = [];
        $sortOrderInputs = (array) ($input['sort_order'] ?? []);
        $requiredInputs = (array) ($input['is_required'] ?? []);
        $labelOverrides = (array) ($input['label_override'] ?? []);
        $now = date('Y-m-d H:i:s');
        foreach ($selectedFieldIds as $index => $fieldId) {
            if (!isset($fieldMap[$fieldId])) {
                continue;
            }
            $rows[] = [
                'field_id' => $fieldId,
                'is_required' => array_key_exists((string) $fieldId, $requiredInputs) ? 1 : 0,
                'sort_order' => max(1, (int) ($sortOrderInputs[$fieldId] ?? ($index + 1))),
                'label_override' => $this->nullable($labelOverrides[$fieldId] ?? null),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        return [[
            'type_name' => $name,
            'type_key' => $key,
            'description' => $description !== '' ? $description : null,
            'display_mode' => $displayMode,
            'input_mode' => $inputMode,
            'created_at' => $now,
            'updated_at' => $now,
        ], $rows];
    }

    private function nullable(mixed $value): ?string
    {
        $text = trim((string) $value);
        return $text === '' ? null : $text;
    }

    private function slugify(string $value): string
    {
        $value = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '_', $value) ?? ''));
        return trim($value, '_');
    }
}
