<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use App\Services\OrderService;

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

try {
    $service = new OrderService();
    $data = $service->getFilteredOrders($_GET);
    
    $result = [
        'success' => true,
        'data' => [
            'orders' => array_map(function($order) {
                return [
                    'id' => $order->id,
                    'title' => $order->title,
                    'cost' => $order->cost,
                    'formatted_cost' => $order->getFormattedCost(),
                    'user' => [
                        'id' => $order->user->id,
                        'name' => $order->user->name
                    ]
                ];
            }, $data['orders']),
            'pagination' => $data['pagination'],
            'stats' => $service->getOrdersStatistics()
        ]
    ];
    
    echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'trace' => $_ENV['APP_ENV'] === 'development' ? $e->getTrace() : null
    ], JSON_UNESCAPED_UNICODE);
}