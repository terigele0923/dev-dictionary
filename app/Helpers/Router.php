<?php

declare(strict_types=1);

namespace App\Helpers;

final class Router
{
    private array $routes = [];

    public function get(string $path, array $action): void
    {
        $this->add('GET', $path, $action);
    }

    public function post(string $path, array $action): void
    {
        $this->add('POST', $path, $action);
    }

    private function add(string $method, string $path, array $action): void
    {
        $this->routes[$method][$path] = $action;
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $action = $this->routes[$method][$path] ?? null;
        if ($action === null) {
            Response::abort(404);
        }

        [$class, $handler] = $action;
        $controller = new $class();
        $controller->{$handler}();
    }
}
