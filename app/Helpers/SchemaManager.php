<?php

declare(strict_types=1);

namespace App\Helpers;

use PDO;

final class SchemaManager
{
    public static function ensure(PDO $pdo): void
    {
        $driver = (string) $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

        self::createAppTables($pdo, $driver);
        self::extendLegacyTables($pdo, $driver);
        self::createCoreTables($pdo, $driver);
        self::seedCategories($pdo);
        self::seedMemoFieldsAndTypes($pdo, $driver);
        self::migrateLegacyEntries($pdo, $driver);
        self::migrateLegacyHistories($pdo);
    }

    private static function createAppTables(PDO $pdo, string $driver): void
    {
        $id = $driver === 'sqlite'
            ? 'INTEGER PRIMARY KEY AUTOINCREMENT'
            : 'INT AUTO_INCREMENT PRIMARY KEY';
        $bool = $driver === 'sqlite' ? 'INTEGER' : 'TINYINT(1)';
        $text = 'TEXT';
        $string = $driver === 'sqlite' ? 'TEXT' : 'VARCHAR(255)';
        $string100 = $driver === 'sqlite' ? 'TEXT' : 'VARCHAR(100)';
        $time = $driver === 'sqlite' ? 'TEXT' : 'DATETIME';
        $int = $driver === 'sqlite' ? 'INTEGER' : 'INT';

        $pdo->exec("CREATE TABLE IF NOT EXISTS users (
            user_id {$id},
            login_id {$string100} NOT NULL,
            user_name {$string100} NOT NULL,
            email {$string} NULL,
            password_hash {$string} NOT NULL,
            role {$string100} NOT NULL DEFAULT 'general',
            is_active {$bool} NOT NULL DEFAULT 1,
            last_login_at {$time} NULL,
            created_at {$time} NOT NULL,
            updated_at {$time} NOT NULL
        )");
        self::ensureUniqueIndex($pdo, $driver, 'users', 'uq_users_login_id', ['login_id']);
        self::ensureUniqueIndex($pdo, $driver, 'users', 'uq_users_email', ['email']);

        $pdo->exec("CREATE TABLE IF NOT EXISTS categories (
            category_id {$id},
            category_name {$string100} NOT NULL,
            description {$text} NULL,
            sort_order {$int} NOT NULL DEFAULT 0,
            is_active {$bool} NOT NULL DEFAULT 1,
            created_at {$time} NOT NULL,
            updated_at {$time} NOT NULL
        )");
        self::ensureUniqueIndex($pdo, $driver, 'categories', 'uq_categories_category_name', ['category_name']);

        $pdo->exec("CREATE TABLE IF NOT EXISTS dictionary_entries (
            entry_id {$id},
            user_id {$int} NOT NULL,
            category_id {$int} NOT NULL,
            title {$string} NOT NULL,
            slug {$string} NOT NULL,
            problem_summary {$text} NULL,
            root_cause {$text} NULL,
            check_points {$text} NULL,
            command_examples {$text} NULL,
            solution_summary {$text} NULL,
            caution_notes {$text} NULL,
            status {$string100} NOT NULL DEFAULT 'draft',
            priority_level {$int} NOT NULL DEFAULT 3,
            version_no {$int} NOT NULL DEFAULT 1,
            published_at {$time} NULL,
            created_at {$time} NOT NULL,
            created_by {$int} NOT NULL,
            updated_at {$time} NOT NULL,
            updated_by {$int} NOT NULL,
            deleted_at {$time} NULL,
            deleted_by {$int} NULL
        )");
        self::ensureUniqueIndex($pdo, $driver, 'dictionary_entries', 'uq_dictionary_entries_user_slug', ['user_id', 'slug']);

        $pdo->exec("CREATE TABLE IF NOT EXISTS dictionary_entry_keywords (
            keyword_id {$id},
            entry_id {$int} NOT NULL,
            keyword {$string100} NOT NULL,
            sort_order {$int} NOT NULL DEFAULT 0,
            created_at {$time} NOT NULL,
            created_by {$int} NOT NULL
        )");
        self::ensureUniqueIndex($pdo, $driver, 'dictionary_entry_keywords', 'uq_dictionary_entry_keywords_entry_keyword', ['entry_id', 'keyword']);

        $pdo->exec("CREATE TABLE IF NOT EXISTS dictionary_entry_histories (
            history_id {$id},
            entry_id {$int} NOT NULL,
            version_no {$int} NOT NULL,
            category_id {$int} NOT NULL,
            title {$string} NOT NULL,
            slug {$string} NOT NULL,
            problem_summary {$text} NULL,
            root_cause {$text} NULL,
            check_points {$text} NULL,
            command_examples {$text} NULL,
            solution_summary {$text} NULL,
            caution_notes {$text} NULL,
            status {$string100} NOT NULL,
            priority_level {$int} NOT NULL,
            keyword_snapshot {$text} NULL,
            snapshot_created_at {$time} NOT NULL,
            snapshot_created_by {$int} NOT NULL
        )");
        self::ensureUniqueIndex($pdo, $driver, 'dictionary_entry_histories', 'uq_dictionary_entry_histories_entry_version', ['entry_id', 'version_no']);
    }

    private static function createCoreTables(PDO $pdo, string $driver): void
    {
        $id = $driver === 'sqlite'
            ? 'INTEGER PRIMARY KEY AUTOINCREMENT'
            : 'INT AUTO_INCREMENT PRIMARY KEY';
        $bool = $driver === 'sqlite' ? 'INTEGER' : 'TINYINT(1)';
        $text = 'TEXT';
        $string = $driver === 'sqlite' ? 'TEXT' : 'VARCHAR(100)';
        $time = $driver === 'sqlite' ? 'TEXT' : 'DATETIME';
        $inputType = $driver === 'sqlite' ? 'TEXT' : 'VARCHAR(30)';

        $pdo->exec("CREATE TABLE IF NOT EXISTS memo_types (
            memo_type_id {$id},
            type_name {$string} NOT NULL,
            type_key {$string} NOT NULL,
            description {$text} NULL,
            display_mode {$string} NOT NULL DEFAULT 'section',
            input_mode {$string} NOT NULL DEFAULT 'section',
            is_active {$bool} NOT NULL DEFAULT 1,
            created_at {$time} NOT NULL,
            updated_at {$time} NOT NULL
        )");
        self::ensureUniqueIndex($pdo, $driver, 'memo_types', 'uq_memo_types_type_name', ['type_name']);
        self::ensureUniqueIndex($pdo, $driver, 'memo_types', 'uq_memo_types_type_key', ['type_key']);

        $pdo->exec("CREATE TABLE IF NOT EXISTS memo_fields (
            field_id {$id},
            field_name {$string} NOT NULL,
            field_key {$string} NOT NULL,
            input_type {$inputType} NOT NULL,
            default_required {$bool} NOT NULL DEFAULT 0,
            is_active {$bool} NOT NULL DEFAULT 1,
            created_at {$time} NOT NULL,
            updated_at {$time} NOT NULL
        )");
        self::ensureUniqueIndex($pdo, $driver, 'memo_fields', 'uq_memo_fields_field_name', ['field_name']);
        self::ensureUniqueIndex($pdo, $driver, 'memo_fields', 'uq_memo_fields_field_key', ['field_key']);

        $pdo->exec("CREATE TABLE IF NOT EXISTS memo_type_fields (
            memo_type_field_id {$id},
            memo_type_id " . ($driver === 'sqlite' ? 'INTEGER' : 'INT') . " NOT NULL,
            field_id " . ($driver === 'sqlite' ? 'INTEGER' : 'INT') . " NOT NULL,
            is_required {$bool} NOT NULL DEFAULT 0,
            sort_order " . ($driver === 'sqlite' ? 'INTEGER' : 'INT') . " NOT NULL DEFAULT 0,
            label_override {$string} NULL,
            created_at {$time} NOT NULL,
            updated_at {$time} NOT NULL
        )");
        self::ensureUniqueIndex($pdo, $driver, 'memo_type_fields', 'uq_memo_type_fields_type_field', ['memo_type_id', 'field_id']);

        $pdo->exec("CREATE TABLE IF NOT EXISTS dictionary_entry_field_values (
            value_id {$id},
            entry_id " . ($driver === 'sqlite' ? 'INTEGER' : 'INT') . " NOT NULL,
            field_id " . ($driver === 'sqlite' ? 'INTEGER' : 'INT') . " NOT NULL,
            row_no " . ($driver === 'sqlite' ? 'INTEGER' : 'INT') . " NOT NULL DEFAULT 1,
            field_value {$text} NULL,
            created_at {$time} NOT NULL,
            updated_at {$time} NOT NULL
        )");
        if (self::indexExists($pdo, $driver, 'dictionary_entry_field_values', 'uq_dictionary_entry_field_values_entry_field')) {
            self::dropIndex($pdo, $driver, 'dictionary_entry_field_values', 'uq_dictionary_entry_field_values_entry_field');
        }
        self::ensureUniqueIndex($pdo, $driver, 'dictionary_entry_field_values', 'uq_dictionary_entry_field_values_entry_field_row', ['entry_id', 'field_id', 'row_no']);
    }

    private static function extendLegacyTables(PDO $pdo, string $driver): void
    {
        $string = $driver === 'sqlite' ? 'TEXT' : 'VARCHAR(100)';

        if (self::tableExists($pdo, $driver, 'dictionary_entries') && !self::columnExists($pdo, $driver, 'dictionary_entries', 'memo_type_id')) {
            $pdo->exec('ALTER TABLE dictionary_entries ADD COLUMN memo_type_id ' . ($driver === 'sqlite' ? 'INTEGER NULL' : 'INT NULL'));
        }

        if (self::tableExists($pdo, $driver, 'memo_types') && !self::columnExists($pdo, $driver, 'memo_types', 'display_mode')) {
            $pdo->exec("ALTER TABLE memo_types ADD COLUMN display_mode {$string} NOT NULL DEFAULT 'section'");
        }
        if (self::tableExists($pdo, $driver, 'memo_types') && !self::columnExists($pdo, $driver, 'memo_types', 'input_mode')) {
            $pdo->exec("ALTER TABLE memo_types ADD COLUMN input_mode {$string} NOT NULL DEFAULT 'section'");
        }
        if (self::tableExists($pdo, $driver, 'dictionary_entry_field_values') && !self::columnExists($pdo, $driver, 'dictionary_entry_field_values', 'row_no')) {
            $pdo->exec('ALTER TABLE dictionary_entry_field_values ADD COLUMN row_no ' . ($driver === 'sqlite' ? 'INTEGER NOT NULL DEFAULT 1' : 'INT NOT NULL DEFAULT 1'));
        }

        if (self::tableExists($pdo, $driver, 'dictionary_entry_histories')) {
            if (!self::columnExists($pdo, $driver, 'dictionary_entry_histories', 'memo_type_id')) {
                $pdo->exec('ALTER TABLE dictionary_entry_histories ADD COLUMN memo_type_id ' . ($driver === 'sqlite' ? 'INTEGER NULL' : 'INT NULL'));
            }
            if (!self::columnExists($pdo, $driver, 'dictionary_entry_histories', 'field_snapshot')) {
                $pdo->exec('ALTER TABLE dictionary_entry_histories ADD COLUMN field_snapshot TEXT NULL');
            }
        }
    }

    private static function seedCategories(PDO $pdo): void
    {
        if (!self::tableExists($pdo, (string) $pdo->getAttribute(PDO::ATTR_DRIVER_NAME), 'categories')) {
            return;
        }

        $count = (int) $pdo->query('SELECT COUNT(*) FROM categories')->fetchColumn();
        if ($count > 0) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        $stmt = $pdo->prepare('INSERT INTO categories (category_name, description, sort_order, is_active, created_at, updated_at) VALUES (:name, :description, :sort_order, 1, :created_at, :updated_at)');
        $categories = [
            ['Linux', 'OSやコマンド関連'],
            ['Git', 'Git / GitHub運用'],
            ['DB', 'MySQL / SQL'],
            ['Troubleshooting', '障害対応や調査メモ'],
            ['Code Reading', 'コード読解や設計理解'],
        ];
        foreach ($categories as $index => $row) {
            $stmt->execute([
                'name' => $row[0],
                'description' => $row[1],
                'sort_order' => $index + 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    private static function seedMemoFieldsAndTypes(PDO $pdo, string $driver): void
    {
        $now = date('Y-m-d H:i:s');
        $fieldSeed = [
            ['問題概要', 'problem_summary', 'textarea', 0],
            ['原因', 'root_cause', 'textarea', 0],
            ['確認ポイント', 'check_points', 'textarea', 0],
            ['コマンド例', 'command_examples', 'textarea', 0],
            ['解決方法', 'solution_summary', 'textarea', 0],
            ['注意点', 'caution_notes', 'textarea', 0],
        ];

        $findField = $pdo->prepare('SELECT field_id FROM memo_fields WHERE field_key = :field_key LIMIT 1');
        $insertField = $pdo->prepare('INSERT INTO memo_fields (field_name, field_key, input_type, default_required, is_active, created_at, updated_at) VALUES (:field_name, :field_key, :input_type, :default_required, 1, :created_at, :updated_at)');
        $fieldIds = [];
        foreach ($fieldSeed as [$name, $key, $inputType, $required]) {
            $findField->execute(['field_key' => $key]);
            $fieldId = $findField->fetchColumn();
            if (!$fieldId) {
                $insertField->execute([
                    'field_name' => $name,
                    'field_key' => $key,
                    'input_type' => $inputType,
                    'default_required' => $required,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $fieldId = (int) $pdo->lastInsertId();
            }
            $fieldIds[$key] = (int) $fieldId;
        }

        $typeKey = 'standard';
        $findType = $pdo->prepare('SELECT memo_type_id FROM memo_types WHERE type_key = :type_key LIMIT 1');
        $findType->execute(['type_key' => $typeKey]);
        $typeId = $findType->fetchColumn();
        if (!$typeId) {
            $insertType = $pdo->prepare('INSERT INTO memo_types (type_name, type_key, description, display_mode, input_mode, is_active, created_at, updated_at) VALUES (:type_name, :type_key, :description, :display_mode, :input_mode, 1, :created_at, :updated_at)');
            $insertType->execute([
                'type_name' => '標準メモ',
                'type_key' => $typeKey,
                'description' => '既存の技術メモ向け標準タイプ',
                'display_mode' => 'section',
                'input_mode' => 'section',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $typeId = (int) $pdo->lastInsertId();
        } else {
            $typeId = (int) $typeId;
        }

        $findRelation = $pdo->prepare('SELECT memo_type_field_id FROM memo_type_fields WHERE memo_type_id = :memo_type_id AND field_id = :field_id LIMIT 1');
        $insertRelation = $pdo->prepare('INSERT INTO memo_type_fields (memo_type_id, field_id, is_required, sort_order, label_override, created_at, updated_at) VALUES (:memo_type_id, :field_id, :is_required, :sort_order, NULL, :created_at, :updated_at)');
        $order = 1;
        foreach (array_keys($fieldIds) as $fieldKey) {
            $findRelation->execute([
                'memo_type_id' => $typeId,
                'field_id' => $fieldIds[$fieldKey],
            ]);
            if (!$findRelation->fetchColumn()) {
                $insertRelation->execute([
                    'memo_type_id' => $typeId,
                    'field_id' => $fieldIds[$fieldKey],
                    'is_required' => 0,
                    'sort_order' => $order,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
            $order++;
        }
    }

    private static function migrateLegacyEntries(PDO $pdo, string $driver): void
    {
        if (!self::tableExists($pdo, $driver, 'dictionary_entries')) {
            return;
        }

        $typeId = (int) ($pdo->query("SELECT memo_type_id FROM memo_types WHERE type_key = 'standard' LIMIT 1")->fetchColumn() ?: 0);
        if ($typeId <= 0) {
            return;
        }

        $pdo->prepare('UPDATE dictionary_entries SET memo_type_id = :memo_type_id WHERE memo_type_id IS NULL')->execute([
            'memo_type_id' => $typeId,
        ]);

        $fieldRows = $pdo->query("SELECT field_id, field_key, field_name, input_type FROM memo_fields WHERE field_key IN ('problem_summary', 'root_cause', 'check_points', 'command_examples', 'solution_summary', 'caution_notes')")->fetchAll() ?: [];
        $fieldMap = [];
        foreach ($fieldRows as $row) {
            $fieldMap[$row['field_key']] = (int) $row['field_id'];
        }

        $entries = $pdo->query('SELECT entry_id, problem_summary, root_cause, check_points, command_examples, solution_summary, caution_notes FROM dictionary_entries')->fetchAll() ?: [];
        $check = $pdo->prepare('SELECT value_id FROM dictionary_entry_field_values WHERE entry_id = :entry_id AND field_id = :field_id AND row_no = 1 LIMIT 1');
        $insert = $pdo->prepare('INSERT INTO dictionary_entry_field_values (entry_id, field_id, row_no, field_value, created_at, updated_at) VALUES (:entry_id, :field_id, :row_no, :field_value, :created_at, :updated_at)');
        $now = date('Y-m-d H:i:s');

        foreach ($entries as $entry) {
            foreach ($fieldMap as $fieldKey => $fieldId) {
                $value = trim((string) ($entry[$fieldKey] ?? ''));
                if ($value === '') {
                    continue;
                }
                $check->execute([
                    'entry_id' => $entry['entry_id'],
                    'field_id' => $fieldId,
                ]);
                if ($check->fetchColumn()) {
                    continue;
                }
                $insert->execute([
                    'entry_id' => $entry['entry_id'],
                    'field_id' => $fieldId,
                    'row_no' => 1,
                    'field_value' => $value,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    private static function migrateLegacyHistories(PDO $pdo): void
    {
        $typeId = (int) ($pdo->query("SELECT memo_type_id FROM memo_types WHERE type_key = 'standard' LIMIT 1")->fetchColumn() ?: 0);
        if ($typeId <= 0) {
            return;
        }

        $pdo->prepare('UPDATE dictionary_entry_histories SET memo_type_id = :memo_type_id WHERE memo_type_id IS NULL')->execute([
            'memo_type_id' => $typeId,
        ]);

        $stmt = $pdo->query('SELECT history_id, problem_summary, root_cause, check_points, command_examples, solution_summary, caution_notes, field_snapshot FROM dictionary_entry_histories');
        $rows = $stmt ? ($stmt->fetchAll() ?: []) : [];
        $update = $pdo->prepare('UPDATE dictionary_entry_histories SET field_snapshot = :field_snapshot WHERE history_id = :history_id');
        foreach ($rows as $row) {
            if (!empty($row['field_snapshot'])) {
                continue;
            }
            $snapshot = [];
            foreach ([
                'problem_summary' => ['問題概要', 'textarea'],
                'root_cause' => ['原因', 'textarea'],
                'check_points' => ['確認ポイント', 'textarea'],
                'command_examples' => ['コマンド例', 'textarea'],
                'solution_summary' => ['解決方法', 'textarea'],
                'caution_notes' => ['注意点', 'textarea'],
            ] as $column => [$label, $inputType]) {
                $value = trim((string) ($row[$column] ?? ''));
                if ($value === '') {
                    continue;
                }
                $snapshot[] = [
                    'label' => $label,
                    'input_type' => $inputType,
                    'value' => $value,
                ];
            }
            $update->execute([
                'field_snapshot' => json_encode($snapshot, JSON_UNESCAPED_UNICODE),
                'history_id' => $row['history_id'],
            ]);
        }
    }

    private static function tableExists(PDO $pdo, string $driver, string $table): bool
    {
        if ($driver === 'sqlite') {
            $stmt = $pdo->prepare("SELECT name FROM sqlite_master WHERE type = 'table' AND name = :table");
            $stmt->execute(['table' => $table]);
            return (bool) $stmt->fetchColumn();
        }

        $stmt = $pdo->prepare('SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table');
        $stmt->execute(['table' => $table]);
        return (bool) $stmt->fetchColumn();
    }

    private static function columnExists(PDO $pdo, string $driver, string $table, string $column): bool
    {
        if ($driver === 'sqlite') {
            $stmt = $pdo->query('PRAGMA table_info(' . $table . ')');
            $columns = $stmt ? ($stmt->fetchAll() ?: []) : [];
            foreach ($columns as $info) {
                if (($info['name'] ?? null) === $column) {
                    return true;
                }
            }
            return false;
        }

        $stmt = $pdo->prepare('SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table AND COLUMN_NAME = :column');
        $stmt->execute([
            'table' => $table,
            'column' => $column,
        ]);
        return (bool) $stmt->fetchColumn();
    }

    private static function indexExists(PDO $pdo, string $driver, string $table, string $index): bool
    {
        if ($driver === 'sqlite') {
            $stmt = $pdo->query('PRAGMA index_list(' . $table . ')');
            $indexes = $stmt ? ($stmt->fetchAll() ?: []) : [];
            foreach ($indexes as $info) {
                if (($info['name'] ?? null) === $index) {
                    return true;
                }
            }
            return false;
        }

        $stmt = $pdo->prepare('SELECT INDEX_NAME FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table AND INDEX_NAME = :index_name');
        $stmt->execute([
            'table' => $table,
            'index_name' => $index,
        ]);
        return (bool) $stmt->fetchColumn();
    }

    private static function ensureUniqueIndex(PDO $pdo, string $driver, string $table, string $index, array $columns): void
    {
        if (self::indexExists($pdo, $driver, $table, $index)) {
            return;
        }

        $columnList = implode(', ', $columns);
        $pdo->exec("CREATE UNIQUE INDEX {$index} ON {$table} ({$columnList})");
    }

    private static function dropIndex(PDO $pdo, string $driver, string $table, string $index): void
    {
        if (!self::indexExists($pdo, $driver, $table, $index)) {
            return;
        }

        if ($driver === 'sqlite') {
            $pdo->exec("DROP INDEX {$index}");
            return;
        }

        $pdo->exec("DROP INDEX {$index} ON {$table}");
    }
}
