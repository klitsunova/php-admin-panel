<?php
namespace App\Core;

class Router
{
    private array $routes = [];

    public function get(string $path, $handler): self
    {
        $this->routes['GET'][$path] = $handler;
        return $this;
    }

    public function dispatch(string $method, string $uri): void
    {
        $handler = $this->routes[$method][$uri] ?? null;

        if ($handler) {
            call_user_func($handler);
        } else {
            http_response_code(404);
            echo '404 Not Found';
        }
    }
}