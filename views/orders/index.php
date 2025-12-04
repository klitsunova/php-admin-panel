<?php 
use App\Models\User;

ob_start(); ?>

<div class="mb-4" id="statsContainer">
    <?php if (isset($stats)): ?>
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="stat-card">
                <div class="stat-icon users">
                    <i class="bi bi-people"></i>
                </div>
                <div class="stat-value"><?php echo $stats['total_users'] ?? 0; ?></div>
                <div class="stat-label">Клиентов</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card">
                <div class="stat-icon orders">
                    <i class="bi bi-cart"></i>
                </div>
                <div class="stat-value"><?php echo $stats['total_orders'] ?? 0; ?></div>
                <div class="stat-label">Заказов</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card">
                <div class="stat-icon revenue">
                    <i class="bi bi-currency-exchange"></i>
                </div>
                <div class="stat-value">
                    <?php 
                        $total = $stats['total_revenue'] ?? 0;
                        echo number_format($total, 0, '.', ' ') . ' BYN';
                    ?>
                </div>
                <div class="stat-label">Общая сумма</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card">
                <div class="stat-icon avg">
                    <i class="bi bi-graph-up"></i>
                </div>
                <div class="stat-value">
                    <?php 
                        $avg = $stats['avg_order_value'] ?? 0;
                        echo number_format($avg, 0, '.', ' ') . ' BYN';
                    ?>
                </div>
                <div class="stat-label">Средний заказ</div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="bi bi-table me-2"></i>
            Список заказов
        </h5>

        <div class="d-flex gap-2">
            <div class="input-group" style="max-width: 300px;">
                <span class="input-group-text">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" 
                       id="filterInput" 
                       class="form-control" 
                       placeholder="Поиск по клиенту..."
                       value="<?php echo htmlspecialchars($filters['search'] ?? ''); ?>">
            </div>
        </div>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="ordersTable">
                <thead>
                    <tr>
                        <th>
                            <a href="#" class="text-decoration-none text-dark" data-sort="orders.id">
                                ID
                                <i class="bi bi-chevron-expand sort-icon"></i>
                            </a>
                        </th>
                        <th>
                            <a href="#" class="text-decoration-none text-dark" data-sort="orders.title">
                                Название
                                <i class="bi bi-chevron-expand sort-icon"></i>
                            </a>
                        </th>
                        <th>
                            <a href="#" class="text-decoration-none text-dark" data-sort="orders.cost">
                                Стоимость
                                <i class="bi bi-chevron-expand sort-icon"></i>
                            </a>
                        </th>
                        <th>
                            <a href="#" class="text-decoration-none text-dark" data-sort="users.name">
                                Клиент
                                <i class="bi bi-chevron-expand sort-icon"></i>
                            </a>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orders)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-4">
                            <div class="empty-state">
                                <i class="bi bi-inbox"></i>
                                <p class="mt-2">Заказы не найдены</p>
                                <small>
                                    <?php if (!empty($filters['search'])): ?>
                                    Попробуйте изменить поисковый запрос
                                    <?php else: ?>
                                    Добавьте первый заказ
                                    <?php endif; ?>
                                </small>
                            </div>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($order->id); ?></td>
                            <td><?php echo htmlspecialchars($order->title); ?></td>
                            <td class="fw-bold text-success">
                                <?php echo $order->getFormattedCost(); ?>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span><?php echo htmlspecialchars($order->user->name); ?></span>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if (isset($pagination) && $pagination['last_page'] > 1): ?>
        <div id="pagination" class="mt-4">
            <nav>
                <ul class="pagination justify-content-center">
                    <?php if ($pagination['current_page'] > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="#" data-page="<?php echo $pagination['current_page'] - 1; ?>">
                            <i class="bi bi-chevron-left"></i>
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php
                    $start = max(1, $pagination['current_page'] - 2);
                    $end = min($pagination['last_page'], $start + 4);

                    if ($end - $start < 4) {
                        $start = max(1, $end - 4);
                    }

                    for ($i = $start; $i <= $end; $i++):
                    ?>
                    <li class="page-item <?php echo $i == $pagination['current_page'] ? 'active' : ''; ?>">
                        <a class="page-link" href="#" data-page="<?php echo $i; ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                    <?php endfor; ?>

                    <?php if ($pagination['current_page'] < $pagination['last_page']): ?>
                    <li class="page-item">
                        <a class="page-link" href="#" data-page="<?php echo $pagination['current_page'] + 1; ?>">
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
                <div class="text-center text-muted mt-2">
                    Показано <?php echo $pagination['from']; ?>-<?php echo $pagination['to']; ?> 
                    из <?php echo $pagination['total']; ?>
                </div>
            </nav>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = 'Управление заказами - ATT';
require __DIR__ . '/../layout.php';
?>
