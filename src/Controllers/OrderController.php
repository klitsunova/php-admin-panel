<?php

namespace App\Controllers;

use App\Services\OrderService;

class OrderController
{
    private OrderService $orderService;
    
    public function __construct()
    {
        $this->orderService = new OrderService();
    }
    
    public function index(array $params = []): void
    {
        $result = $this->orderService->getFilteredOrders($params);
        
        echo json_encode([
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
                }, $result['orders']),
                'pagination' => $result['pagination'],
                'filters' => $result['filters']
            ]
        ], JSON_UNESCAPED_UNICODE);
    }
    
    public function show(): void
    {
        $result = $this->orderService->getFilteredOrders($_GET);
        $stats = $this->orderService->getOrdersStatistics();
        
        extract([
            'orders' => $result['orders'],
            'pagination' => $result['pagination'],
            'filters' => $result['filters'],
            'stats' => $stats
        ]);
        
        require __DIR__ . '/../../views/orders/index.php';
    }
}