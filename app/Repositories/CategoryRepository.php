<?php

declare(strict_types=1);

namespace App\Repositories;

final class CategoryRepository extends BaseRepository
{
    public function allActive(): array
    {
        $sql = 'SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order ASC, category_name ASC';
        return $this->pdo()->query($sql)->fetchAll() ?: [];
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
}
