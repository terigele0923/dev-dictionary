<?php

declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap/app.php';

\App\Helpers\Database::connection();

echo "Application schema is ready.\n";
