<?php
require_once __DIR__ . '/auth-check.php';

// Handle delete
if (isset($_GET['delete'])) {
    $deleteId = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$deleteId]);
    header('Location: manage-products.php');
    exit;
}

require_once __DIR__ . '/../includes/header.php';

$products = $pdo->query("
    SELECT products.*, categories.name AS category_name
    FROM products
    LEFT JOIN categories ON products.category_id = categories.id
    ORDER BY products.created_at DESC
")->fetchAll();
?>

<h1>Manage Products</h1>

<a href="add-product.php" class="admin-btn" style="margin-bottom: 15px; display: inline-block;">+ Add Product</a>

<?php if (empty($products)): ?>
    <p>No products yet.</p>
<?php else: ?>
    <table class="cart-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Category</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $product): ?>
                <tr>
                    <td><?= htmlspecialchars($product['name']) ?></td>
                    <td><?= htmlspecialchars($product['category_name'] ?? 'Uncategorized') ?></td>
                    <td>$<?= number_format($product['price'], 2) ?></td>
                    <td><?= (int)$product['stock'] ?></td>
                    <td>
                        <a href="edit-product.php?id=<?= (int)$product['id'] ?>" class="edit-link">Edit</a>
                        <a href="manage-products.php?delete=<?= (int)$product['id'] ?>"
                           class="remove-link"
                           onclick="return confirm('Delete this product? This cannot be undone.')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>