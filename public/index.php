<?php

require_once __DIR__ . '/../vendor/autoload.php';

if (file_exists(__DIR__ . '/../.env')) {
    $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        $_ENV[$name] = $value;
    }
}

use App\Core\App;
use App\Core\Router;

$app = App::getInstance();

$router = new Router();

$router->get('/', 'OrderController@index');
$router->get('/orders', 'OrderController@index');

$router->get('/api/orders', 'OrderController@api');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    http_response_code(200);
    exit;
}

try {
    $router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
} catch (Exception $e) {
    http_response_code(500);

    if ($app->isDebug()) {
        echo json_encode([
            'error' => $e->getMessage(),
            'trace' => $e->getTrace()
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(['error' => 'Internal server error']);
    }
}
