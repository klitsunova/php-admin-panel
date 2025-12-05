<?php

namespace App\Controllers;

abstract class Controller
{
    protected function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    protected function view(string $view, array $data = []): void
    {
        extract($data);

        $viewFile = __DIR__ . "/../../views/{$view}.php";

        if (!file_exists($viewFile)) {
            throw new \Exception("View {$view} not found");
        }

        require $viewFile;
    }
}
