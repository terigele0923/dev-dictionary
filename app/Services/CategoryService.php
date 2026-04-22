<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\CategoryRepository;

final class CategoryService
{
    public function __construct(private readonly CategoryRepository $categories = new CategoryRepository())
    {
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
        $this->categories->create($name, $description ? trim($description) : null);
    }
}
