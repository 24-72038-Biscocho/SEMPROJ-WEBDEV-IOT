<?php
declare(strict_types=1);

namespace App\Core;

final class Router
{
    private array $routes = [];

    public function get(string $path, array $handler): void
    {
        $this->add('GET', $path, $handler);
    }

    public function post(string $path, array $handler): void
    {
        $this->add('POST', $path, $handler);
    }

    public function dispatch(string $method, string $path): void
    {
        $path = rtrim($path, '/') ?: '/';
        $handler = $this->routes[strtoupper($method) . ' ' . $path] ?? null;

        if ($handler === null) {
            http_response_code(404);
            echo '404 - Page not found';
            return;
        }

        [$controllerClass, $action] = $handler;
        (new $controllerClass())->$action();
    }

    private function add(string $method, string $path, array $handler): void
    {
        $path = rtrim($path, '/') ?: '/';
        $this->routes[$method . ' ' . $path] = $handler;
    }
}
