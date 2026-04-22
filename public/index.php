<?php

declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap/app.php';
require dirname(__DIR__) . '/app/Helpers/functions.php';

use App\Helpers\Router;

$router = new Router();
require dirname(__DIR__) . '/routes/web.php';
$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
