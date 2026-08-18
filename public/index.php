<?php require_once __DIR__ . '/../includes/header.php'; ?>

<h1>Welcome to PhoneHub</h1>
<p>Your one-stop shop for phones and accessories.</p>

<div class="product-grid">
    <?php
    $stmt = $pdo->query("SELECT * FROM products ORDER BY created_at DESC");
    $products = $stmt->fetchAll();

    foreach ($products as $product):
    ?>
        <div class="product-card">
    <?php if ($product['image']): ?>
        <img src="images/products/<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="product-thumb">
    <?php endif; ?>
    <h3><?= htmlspecialchars($product['name']) ?></h3>
    <p>$<?= number_format($product['price'], 2) ?></p>
    <a href="product-details.php?slug=<?= urlencode($product['slug']) ?>">View Details</a>
</div>
    <?php endforeach; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>