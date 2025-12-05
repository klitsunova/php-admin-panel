<?php

namespace App\Core;

class Router
{
    private array $routes = [];

    public function get(string $path, string $controllerAction): void
    {
        $this->routes['GET'][$path] = $controllerAction;
    }

    public function dispatch(string $method, string $uri): void
    {
        $uri = parse_url($uri, PHP_URL_PATH);
        $method = strtoupper($method);

        if (!isset($this->routes[$method][$uri])) {
            $this->notFound();
            return;
        }

        [$controller, $action] = explode('@', $this->routes[$method][$uri]);

        $controllerClass = "App\\Controllers\\{$controller}";

        if (!class_exists($controllerClass)) {
            throw new \Exception("Controller {$controllerClass} not found");
        }

        $controllerInstance = new $controllerClass();

        if (!method_exists($controllerInstance, $action)) {
            throw new \Exception("Method {$action} not found in {$controllerClass}");
        }

        call_user_func([$controllerInstance, $action]);
    }

    private function notFound(): void
    {
        http_response_code(404);

        if ($this->isApiRequest()) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Not Found']);
        } else {
            echo '<h1>404 - Page Not Found</h1>';
        }
    }

    private function isApiRequest(): bool
    {
        return strpos($_SERVER['REQUEST_URI'], '/api/') === 0;
    }
}
