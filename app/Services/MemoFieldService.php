<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\MemoFieldRepository;

final class MemoFieldService
{
    public function __construct(private readonly MemoFieldRepository $fields = new MemoFieldRepository())
    {
    }

    public function list(): array
    {
        return $this->fields->all();
    }

    public function listActive(): array
    {
        return $this->fields->allActive();
    }

    public function find(int $fieldId): ?array
    {
        return $this->fields->find($fieldId);
    }

    public function create(array $input): void
    {
        $this->fields->create($this->normalize($input));
    }

    public function update(int $fieldId, array $input): void
    {
        $existing = $this->fields->find($fieldId);
        if (!$existing) {
            throw new \RuntimeException('対象の項目が見つかりません。');
        }

        $data = $this->normalize($input, $fieldId);
        unset($data['created_at']);
        $this->fields->update($fieldId, $data);
    }

    public function delete(int $fieldId): void
    {
        $existing = $this->fields->find($fieldId);
        if (!$existing) {
            throw new \RuntimeException('対象の項目が見つかりません。');
        }
        if ($this->fields->countTypeLinks($fieldId) > 0 || $this->fields->countValues($fieldId) > 0) {
            throw new \RuntimeException('使用中の項目は削除できません。');
        }
        $this->fields->delete($fieldId);
    }

    private function slugify(string $value): string
    {
        $value = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '_', $value) ?? ''));
        return trim($value, '_');
    }

    private function normalize(array $input, ?int $ignoreFieldId = null): array
    {
        $name = trim((string) ($input['field_name'] ?? ''));
        $key = trim((string) ($input['field_key'] ?? ''));
        $inputType = trim((string) ($input['input_type'] ?? 'text'));
        $defaultRequired = !empty($input['default_required']) ? 1 : 0;

        if ($name === '') {
            throw new \InvalidArgumentException('項目名は必須です。');
        }
        if (mb_strlen($name) > 100) {
            throw new \InvalidArgumentException('項目名は100文字以内です。');
        }

        if ($key === '') {
            $key = $this->slugify($name);
        }
        if (!preg_match('/^[a-z][a-z0-9_]*$/', $key)) {
            throw new \InvalidArgumentException('項目キーは英小文字・数字・アンダースコアで入力してください。');
        }
        $byKey = $this->fields->findByKey($key);
        if ($byKey && (int) $byKey['field_id'] !== $ignoreFieldId) {
            throw new \InvalidArgumentException('同じ項目キーが既に存在します。');
        }

        if (!in_array($inputType, ['text', 'textarea', 'number', 'date'], true)) {
            throw new \InvalidArgumentException('入力種別が不正です。');
        }

        $now = date('Y-m-d H:i:s');
        return [
            'field_name' => $name,
            'field_key' => $key,
            'input_type' => $inputType,
            'default_required' => $defaultRequired,
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }
}
