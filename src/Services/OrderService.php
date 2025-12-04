<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;

class OrderService
{
    private Database $db;
    private int $perPage = 10;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    public function getFilteredOrders(array $filters = []): array
    {
        $page = max(1, (int)($filters['page'] ?? 1));
        $search = trim($filters['search'] ?? '');
        $sortBy = $this->validateSortColumn($filters['sort'] ?? 'orders.id');
        $sortOrder = $this->validateSortOrder($filters['order'] ?? 'DESC');
        
        $offset = ($page - 1) * $this->perPage;
        
        $countSql = "SELECT COUNT(*) as total 
                     FROM orders 
                     JOIN users ON orders.user_id = users.id 
                     WHERE 1=1";
        
        $dataSql = "SELECT 
                        orders.id,
                        orders.title,
                        orders.cost,
                        orders.user_id,
                        users.id as user__id,
                        users.name as user__name
                    FROM orders 
                    JOIN users ON orders.user_id = users.id 
                    WHERE 1=1";
        
        $params = [];
        $countParams = [];
        
        if ($search !== '') {
            $dataSql .= " AND users.name LIKE ?";
            $countSql .= " AND users.name LIKE ?";
            $params[] = "%{$search}%";
            $countParams[] = "%{$search}%";
        }
        
        $dataSql .= " ORDER BY {$sortBy} {$sortOrder}";
        
        $dataSql .= " LIMIT ? OFFSET ?";
        $params[] = $this->perPage;
        $params[] = $offset;
        
        $ordersData = $this->db->query($dataSql, $params);
        
        $countResult = $this->db->query($countSql, $countParams);
        $total = (int)$countResult[0]['total'] ?? 0;
        
        $orders = [];
        foreach ($ordersData as $data) {
            $order = new Order((array)$data);
            $order->user = new User([
                'id' => $data['user__id'],
                'name' => $data['user__name']
            ]);
            $orders[] = $order;
        }
        
        return [
            'orders' => $orders,
            'pagination' => [
                'total' => $total,
                'per_page' => $this->perPage,
                'current_page' => $page,
                'last_page' => ceil($total / $this->perPage),
                'from' => $offset + 1,
                'to' => min($offset + $this->perPage, $total)
            ],
            'filters' => [
                'search' => $search,
                'sort' => $sortBy,
                'order' => $sortOrder
            ]
        ];
    }
    
    public function getOrdersStatistics(): array
    {
        $sql = "SELECT 
                    COUNT(DISTINCT users.id) as total_users,
                    COUNT(orders.id) as total_orders,
                    SUM(orders.cost) as total_revenue,
                    AVG(orders.cost) as avg_order_value,
                    MAX(orders.cost) as max_order,
                    MIN(orders.cost) as min_order
                FROM users 
                LEFT JOIN orders ON users.id = orders.user_id";
        
        $result = $this->db->query($sql);
        return $result[0] ?? [];
    }

    private function validateSortColumn(string $column): string
    {
        $allowed = [
            'orders.id' => 'orders.id',
            'orders.title' => 'orders.title',
            'orders.cost' => 'orders.cost',
            'users.name' => 'users.name'
        ];
        
        return $allowed[$column] ?? 'orders.id';
    }
    
    private function validateSortOrder(string $order): string
    {
        $order = strtoupper($order);
        return in_array($order, ['ASC', 'DESC']) ? $order : 'DESC';
    }
}