<?php

declare(strict_types=1);

$root = dirname(__DIR__);
if (!is_file($root . '/.env') && is_file($root . '/.env.example')) {
    copy($root . '/.env.example', $root . '/.env');
    $env = file_get_contents($root . '/.env');
    $env = str_replace('/absolute/path/to/project/storage/data/database.sqlite', $root . '/storage/data/database.sqlite', $env);
    file_put_contents($root . '/.env', $env);
}

require $root . '/bootstrap/app.php';

$pdo = \App\Helpers\Database::connection();
$sql = file_get_contents($root . '/database/schema_sqlite.sql');
$pdo->exec($sql);

$now = date('Y-m-d H:i:s');
$categories = [
    ['Linux', 'OSやコマンド関連'],
    ['Git', 'Git / GitHub運用'],
    ['DB', 'MySQL / SQL'],
    ['Troubleshooting', '障害対応や調査メモ'],
    ['Code Reading', 'コード読解や設計理解'],
];
$stmt = $pdo->prepare('INSERT OR IGNORE INTO categories (category_name, description, sort_order, is_active, created_at, updated_at) VALUES (:name, :description, :sort_order, 1, :created_at, :updated_at)');
foreach ($categories as $index => $row) {
    $stmt->execute([
        'name' => $row[0],
        'description' => $row[1],
        'sort_order' => $index + 1,
        'created_at' => $now,
        'updated_at' => $now,
    ]);
}

echo "SQLite database initialized.\n";
