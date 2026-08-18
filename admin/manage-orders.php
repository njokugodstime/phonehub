<?php
require_once __DIR__ . '/auth-check.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['status'])) {
    $validStatuses = ['pending', 'processing', 'shipped', 'completed', 'cancelled'];
    if (in_array($_POST['status'], $validStatuses)) {
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$_POST['status'], (int)$_POST['order_id']]);
    }
    header('Location: manage-orders.php');
    exit;
}

require_once __DIR__ . '/../includes/header.php';

$orders = $pdo->query("
    SELECT orders.*, users.name AS customer_name, users.email AS customer_email
    FROM orders
    JOIN users ON orders.user_id = users.id
    ORDER BY orders.created_at DESC
")->fetchAll();
?>

<h1>Manage Orders</h1>

<?php if (empty($orders)): ?>
    <p>No orders yet.</p>
<?php else: ?>
    <table class="cart-table">
        <thead>
            <tr>
                <th>Order #</th>
                <th>Customer</th>
                <th>Total</th>
                <th>Status</th>
                <th>Date</th>
                <th>Update</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order): ?>
                <tr>
                    <td>#<?= (int)$order['id'] ?></td>
                    <td><?= htmlspecialchars($order['customer_name']) ?><br><small><?= htmlspecialchars($order['customer_email']) ?></small></td>
                    <td>$<?= number_format($order['total'], 2) ?></td>
                    <td><?= htmlspecialchars(ucfirst($order['status'])) ?></td>
                    <td><?= htmlspecialchars($order['created_at']) ?></td>
                    <td>
                        <form method="POST" class="status-form">
                            <input type="hidden" name="order_id" value="<?= (int)$order['id'] ?>">
                            <select name="status" onchange="this.form.submit()">
                                <?php foreach (['pending','processing','shipped','completed','cancelled'] as $status): ?>
                                    <option value="<?= $status ?>" <?= $order['status'] === $status ? 'selected' : '' ?>>
                                        <?= ucfirst($status) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>