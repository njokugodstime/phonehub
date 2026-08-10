<?php require_once __DIR__ . '/../includes/header.php'; ?>

<?php
if (!isset($_GET['slug']) || $_GET['slug'] === '') {
    echo "<p>Product not found.</p>";
} else {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE slug = ?");
    $stmt->execute([$_GET['slug']]);
    $product = $stmt->fetch();

    if (!$product) {
        echo "<p>Product not found.</p>";
    } else {
?>
    <div class="product-details">
        <h1><?= htmlspecialchars($product['name']) ?></h1>
        <p class="price">$<?= number_format($product['price'], 2) ?></p>
        <p class="description"><?= nl2br(htmlspecialchars($product['description'])) ?></p>
        <p class="stock">
            <?php if ($product['stock'] > 0): ?>
                In Stock (<?= (int)$product['stock'] ?> available)
            <?php else: ?>
                Out of Stock
            <?php endif; ?>
        </p>

        <?php if ($product['stock'] > 0): ?>
            <form action="cart.php" method="POST" class="add-to-cart-form">
                <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">
                <label for="quantity">Quantity:</label>
                <input type="number" name="quantity" id="quantity" value="1" min="1" max="<?= (int)$product['stock'] ?>">
                <button type="submit">Add to Cart</button>
            </form>
        <?php endif; ?>

        <a href="products.php" class="back-link">&larr; Back to Products</a>
    </div>
<?php
    }
}
?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>