<?php

declare(strict_types=1);

namespace App\Helpers;

use PDO;

final class Database
{
    private static ?PDO $pdo = null;

    public static function connection(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $config = Config::get('database');
        $connection = $config['connection'] ?? 'sqlite';

        if ($connection === 'mysql') {
            $mysql = $config['mysql'];
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                $mysql['host'],
                $mysql['port'],
                $mysql['database'],
                $mysql['charset']
            );
            self::$pdo = new PDO($dsn, $mysql['username'], $mysql['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
            return self::$pdo;
        }

        $sqlite = $config['sqlite'];
        $databaseFile = $sqlite['database'];
        $dir = dirname($databaseFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        if (!is_file($databaseFile)) {
            touch($databaseFile);
        }
        self::$pdo = new PDO('sqlite:' . $databaseFile, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        self::$pdo->exec('PRAGMA foreign_keys = ON');

        return self::$pdo;
    }
}
