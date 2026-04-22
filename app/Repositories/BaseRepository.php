<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Helpers\Database;
use PDO;

abstract class BaseRepository
{
    private ?PDO $pdo = null;

    protected function pdo(): PDO
    {
        if (!$this->pdo instanceof PDO) {
            $this->pdo = Database::connection();
        }
        return $this->pdo;
    }
}
