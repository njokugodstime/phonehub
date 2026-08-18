<?php require_once __DIR__ . '/../includes/header.php'; ?>

<h1>All Products</h1>

<div class="filter-bar">
    <a href="products.php" class="filter-btn <?= !isset($_GET['category']) ? 'active' : '' ?>">All</a>
    <?php
    $categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
    foreach ($categories as $cat):
        $isActive = isset($_GET['category']) && $_GET['category'] === $cat['slug'];
    ?>
        <a href="products.php?category=<?= urlencode($cat['slug']) ?>" class="filter-btn <?= $isActive ? 'active' : '' ?>">
            <?= htmlspecialchars($cat['name']) ?>
        </a>
    <?php endforeach; ?>
</div>

<div class="product-grid">
    <?php
    if (isset($_GET['category']) && $_GET['category'] !== '') {
        $stmt = $pdo->prepare("
            SELECT products.* FROM products
            JOIN categories ON products.category_id = categories.id
            WHERE categories.slug = ?
            ORDER BY products.created_at DESC
        ");
        $stmt->execute([$_GET['category']]);
    } else {
        $stmt = $pdo->query("SELECT * FROM products ORDER BY created_at DESC");
    }
    $products = $stmt->fetchAll();

    if (count($products) === 0):
    ?>
        <p>No products found in this category.</p>
    <?php else: ?>
        <?php foreach ($products as $product): ?>
            <div class="product-card">
    <?php if ($product['image']): ?>
        <img src="images/products/<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="product-thumb">
    <?php endif; ?>
    <h3><?= htmlspecialchars($product['name']) ?></h3>
    <p>$<?= number_format($product['price'], 2) ?></p>
    <a href="product-details.php?slug=<?= urlencode($product['slug']) ?>">View Details</a>
</div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>