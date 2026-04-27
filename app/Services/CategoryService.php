<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\CategoryRepository;

final class CategoryService
{
    public function __construct(private readonly CategoryRepository $categories = new CategoryRepository())
    {
    }

    public function all(): array
    {
        return $this->categories->all();
    }

    public function list(): array
    {
        return $this->categories->allActive();
    }

    public function create(string $name, ?string $description): void
    {
        $name = trim($name);
        if ($name === '') {
            throw new \InvalidArgumentException('カテゴリ名は必須です。');
        }
        if (mb_strlen($name) > 100) {
            throw new \InvalidArgumentException('カテゴリ名は100文字以内です。');
        }
        if ($this->categories->existsByName($name)) {
            throw new \InvalidArgumentException('同じカテゴリ名が既に存在します。');
        }
        $this->categories->create($name, $description ? trim($description) : null);
    }

    public function find(int $categoryId): ?array
    {
        return $this->categories->find($categoryId);
    }

    public function update(int $categoryId, string $name, ?string $description): void
    {
        $existing = $this->categories->find($categoryId);
        if (!$existing) {
            throw new \RuntimeException('対象のカテゴリが見つかりません。');
        }

        $name = trim($name);
        if ($name === '') {
            throw new \InvalidArgumentException('カテゴリ名は必須です。');
        }
        if (mb_strlen($name) > 100) {
            throw new \InvalidArgumentException('カテゴリ名は100文字以内です。');
        }
        if ($this->categories->existsByName($name, $categoryId)) {
            throw new \InvalidArgumentException('同じカテゴリ名が既に存在します。');
        }

        $this->categories->update($categoryId, [
            'category_name' => $name,
            'description' => $description ? trim($description) : null,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function delete(int $categoryId): void
    {
        $existing = $this->categories->find($categoryId);
        if (!$existing) {
            throw new \RuntimeException('対象のカテゴリが見つかりません。');
        }
        if ($this->categories->countEntries($categoryId) > 0) {
            throw new \RuntimeException('使用中のカテゴリは削除できません。');
        }
        $this->categories->delete($categoryId);
    }
}
