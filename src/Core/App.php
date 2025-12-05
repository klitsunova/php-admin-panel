<?php

namespace App\Core;

class App
{
    private static $instance = null;
    private $config = [];

    private function __construct()
    {
        $this->loadConfig();
        $this->initErrorHandling();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function loadConfig(): void
    {
        $this->config = [
            'name' => 'ATT Orders App',
            'env' => $_ENV['APP_ENV'] ?? 'production',
            'debug' => ($_ENV['APP_ENV'] ?? 'production') === 'development',
            'url' => $_ENV['APP_URL'] ?? 'http://localhost:8080',
            'timezone' => 'Europe/Moscow',
            'pagination' => [
                'per_page' => 10
            ]
        ];

        date_default_timezone_set($this->config['timezone']);
    }

    private function initErrorHandling(): void
    {
        if ($this->config['debug']) {
            error_reporting(E_ALL);
            ini_set('display_errors', '1');
        } else {
            error_reporting(0);
            ini_set('display_errors', '0');
        }
    }

    public function config(string $key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }

    public function isDebug(): bool
    {
        return $this->config['debug'];
    }
}
