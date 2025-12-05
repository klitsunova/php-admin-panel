<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? 'ATT Orders') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="/">
                <i class="bi bi-bag-check me-2"></i>
                ATT Orders
            </a>
            <div class="d-flex align-items-center">
                <span class="badge bg-light text-dark me-3">
                    <i class="bi bi-clock me-1"></i>
                    <?php echo date('H:i'); ?>
                </span>
                <?php if (isset($stats['total_orders'])): ?>
                <span class="badge bg-primary">
                    <i class="bi bi-cart me-1"></i>
                    <?php echo $stats['total_orders']; ?> заказов
                </span>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container">
        <?php echo $content ?? ''; ?>
    </div>

    <footer class="mt-5 py-4 border-top">
        <div class="container text-center text-muted">
            <small>
                &copy; <?php echo date('Y'); ?> Автоматизированные технологии туризма
                <span class="mx-2">•</span>
                Тестовое задание PHP-разработчик
            </small>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/app.js"></script>

    <div id="loadingIndicator" class="loading-overlay" style="display: none;">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Загрузка...</span>
        </div>
    </div>
</body>
</html>
