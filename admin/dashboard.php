<?php
require_once __DIR__ . '/auth-check.php';
require_once __DIR__ . '/../includes/header.php';

$totalProducts = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$totalOrders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn();
$totalRevenue = $pdo->query("SELECT COALESCE(SUM(total), 0) FROM orders WHERE status != 'cancelled'")->fetchColumn();
?>

<h1>Admin Dashboard</h1>

<div class="admin-stats">
    <div class="stat-card">
        <h3>Total Products</h3>
        <p><?= (int)$totalProducts ?></p>
    </div>
    <div class="stat-card">
        <h3>Total Orders</h3>
        <p><?= (int)$totalOrders ?></p>
    </div>
    <div class="stat-card">
        <h3>Total Customers</h3>
        <p><?= (int)$totalUsers ?></p>
    </div>
    <div class="stat-card">
        <h3>Total Revenue</h3>
        <p>$<?= number_format($totalRevenue, 2) ?></p>
    </div>
</div>

<div class="admin-links">
    <a href="add-product.php" class="admin-btn">+ Add Product</a>
    <a href="manage-orders.php" class="admin-btn">Manage Orders</a>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>