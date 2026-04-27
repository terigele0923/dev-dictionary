<?php

declare(strict_types=1);

namespace App\Repositories;

final class CategoryRepository extends BaseRepository
{
    public function all(): array
    {
        $sql = 'SELECT * FROM categories ORDER BY is_active DESC, sort_order ASC, category_name ASC';
        return $this->pdo()->query($sql)->fetchAll() ?: [];
    }

    public function allActive(): array
    {
        $sql = 'SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order ASC, category_name ASC';
        return $this->pdo()->query($sql)->fetchAll() ?: [];
    }

    public function find(int $categoryId): ?array
    {
        $stmt = $this->pdo()->prepare('SELECT * FROM categories WHERE category_id = :category_id LIMIT 1');
        $stmt->execute(['category_id' => $categoryId]);
        return $stmt->fetch() ?: null;
    }

    public function create(string $name, ?string $description = null): void
    {
        $count = (int) $this->pdo()->query('SELECT COUNT(*) FROM categories')->fetchColumn();
        $stmt = $this->pdo()->prepare('INSERT INTO categories (category_name, description, sort_order, is_active, created_at, updated_at) VALUES (:name, :description, :sort_order, 1, :created_at, :updated_at)');
        $stmt->execute([
            'name' => $name,
            'description' => $description,
            'sort_order' => $count + 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function update(int $categoryId, array $data): void
    {
        $stmt = $this->pdo()->prepare('UPDATE categories SET category_name = :category_name, description = :description, updated_at = :updated_at WHERE category_id = :category_id');
        $stmt->execute([
            'category_name' => $data['category_name'],
            'description' => $data['description'],
            'updated_at' => $data['updated_at'],
            'category_id' => $categoryId,
        ]);
    }

    public function delete(int $categoryId): void
    {
        $stmt = $this->pdo()->prepare('DELETE FROM categories WHERE category_id = :category_id');
        $stmt->execute(['category_id' => $categoryId]);
    }

    public function existsByName(string $name, ?int $ignoreCategoryId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM categories WHERE category_name = :category_name';
        $params = ['category_name' => $name];
        if ($ignoreCategoryId !== null) {
            $sql .= ' AND category_id <> :category_id';
            $params['category_id'] = $ignoreCategoryId;
        }
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function countEntries(int $categoryId): int
    {
        $stmt = $this->pdo()->prepare('SELECT COUNT(*) FROM dictionary_entries WHERE category_id = :category_id AND deleted_at IS NULL');
        $stmt->execute(['category_id' => $categoryId]);
        return (int) $stmt->fetchColumn();
    }
}
