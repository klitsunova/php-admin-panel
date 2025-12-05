<?php

namespace App\Controllers;

use App\Core\Database;
use App\Models\Order;
use App\Models\User;

class OrderController extends Controller
{
    private $db;
    private int $perPage = 10;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function index(): void
    {
        $data = $this->getOrdersData($_GET);
        $stats = $this->getStatistics();

        $this->view('orders/index', [
            'orders' => $data['orders'],
            'pagination' => $data['pagination'],
            'filters' => $data['filters'],
            'stats' => $stats,
            'title' => 'Управление заказами'
        ]);
    }

    public function api(): void
    {
        $data = $this->getOrdersData($_GET);
        $stats = $this->getStatistics();

        $this->json([
            'success' => true,
            'data' => [
                'orders' => array_map(function($order) {
                    return [
                        'id' => $order->id,
                        'title' => $order->title,
                        'cost' => (float)$order->cost,
                        'formatted_cost' => $order->getFormattedCost(),
                        'user' => [
                            'id' => $order->user->id,
                            'name' => $order->user->name
                        ]
                    ];
                }, $data['orders']),
                'pagination' => $data['pagination'],
                'stats' => $stats
            ]
        ]);
    }

    private function getOrdersData(array $filters = []): array
    {
        $page = max(1, (int)($filters['page'] ?? 1));
        $search = trim($filters['search'] ?? '');
        $sortBy = $filters['sort'] ?? 'orders.id';
        $sortOrder = $filters['order'] ?? 'DESC';

        $orders = $this->getOrders($page, $this->perPage, $search, $sortBy, $sortOrder);

        $total = $this->countOrders($search);

        return [
            'orders' => $orders,
            'pagination' => [
                'total' => $total,
                'per_page' => $this->perPage,
                'current_page' => $page,
                'last_page' => max(1, ceil($total / $this->perPage)),
                'from' => ($page - 1) * $this->perPage + 1,
                'to' => min($page * $this->perPage, $total)
            ],
            'filters' => [
                'search' => $search,
                'sort' => $sortBy,
                'order' => $sortOrder
            ]
        ];
    }

    private function getOrders(
        int $page = 1,
        int $perPage = 10,
        string $search = '',
        string $sortBy = 'orders.id',
        string $sortOrder = 'DESC'
    ): array {
        $offset = ($page - 1) * $perPage;

        $where = '';
        $params = [];

        if (!empty($search)) {
            $where = "WHERE users.name LIKE :search";
            $params['search'] = "%{$search}%";
        }

        $allowedSort = ['orders.id', 'orders.title', 'orders.cost', 'users.name'];
        $sortBy = in_array($sortBy, $allowedSort) ? $sortBy : 'orders.id';
        $sortOrder = strtoupper($sortOrder) === 'ASC' ? 'ASC' : 'DESC';

        $sql = "SELECT 
                    orders.*,
                    users.id as user_id,
                    users.name as user_name
                FROM orders 
                INNER JOIN users ON orders.user_id = users.id
                {$where}
                ORDER BY {$sortBy} {$sortOrder}
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }

        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        $orders = [];
        foreach ($stmt->fetchAll() as $data) {
            $order = new Order([
                'id' => $data['id'],
                'title' => $data['title'],
                'cost' => $data['cost'],
                'user_id' => $data['user_id']
            ]);

            $order->user = new User([
                'id' => $data['user_id'],
                'name' => $data['user_name']
            ]);

            $orders[] = $order;
        }

        return $orders;
    }

    private function countOrders(string $search = ''): int
    {
        $where = '';
        $params = [];

        if (!empty($search)) {
            $where = "WHERE users.name LIKE :search";
            $params['search'] = "%{$search}%";
        }

        $sql = "SELECT COUNT(*) as total
                FROM orders 
                INNER JOIN users ON orders.user_id = users.id
                {$where}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $result = $stmt->fetch();
        return (int)($result['total'] ?? 0);
    }

    private function getStatistics(): array
    {
        $sql = "SELECT 
                    COUNT(*) as total_users,
                    (SELECT COUNT(*) FROM orders) as total_orders,
                    (SELECT SUM(cost) FROM orders) as total_revenue,
                    (SELECT AVG(cost) FROM orders) as avg_order_value
                FROM users";

        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();

        return $result ?: [
            'total_users' => 0,
            'total_orders' => 0,
            'total_revenue' => 0,
            'avg_order_value' => 0
        ];
    }
}
