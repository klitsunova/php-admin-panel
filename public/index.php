<?php
require_once __DIR__ . '/../vendor/autoload.php';

$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;

        list($name, $value) = explode('=', $line, 2);
        $_ENV[$name] = $value;
        putenv("$name=$value");
    }
}

use App\Core\App;
use App\Core\Router;

$app = App::getInstance();

$router = new Router();

$router->get('/api/orders', 'OrderController@indexApi');

$router->get('/', 'OrderController@show');
$router->get('/orders', 'OrderController@show');


if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, PUT, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    http_response_code(200);
    exit;
}

try {
    $router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
} catch (Exception $e) {
    http_response_code(500);

    if ($app->isDebug()) {
        header('Content-Type: application/json');
        echo json_encode([
            'error' => $e->getMessage(),
            'trace' => $e->getTrace()
        ]);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Internal server error']);
    }
}