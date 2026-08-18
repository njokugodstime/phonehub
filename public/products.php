<?php require_once __DIR__ . '/../includes/header.php'; ?>

<h1>All Products</h1>

<form method="GET" class="search-bar">
    <input type="text" name="search" placeholder="Search products..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
    <?php if (isset($_GET['category'])): ?>
        <input type="hidden" name="category" value="<?= htmlspecialchars($_GET['category']) ?>">
    <?php endif; ?>
    <button type="submit">Search</button>
</form>

<div class="filter-bar">
    <a href="products.php<?= isset($_GET['search']) ? '?search=' . urlencode($_GET['search']) : '' ?>" class="filter-btn <?= !isset($_GET['category']) ? 'active' : '' ?>">All</a>
    <?php
    $categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
    foreach ($categories as $cat):
        $isActive = isset($_GET['category']) && $_GET['category'] === $cat['slug'];
        $searchParam = isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : '';
    ?>
        <a href="products.php?category=<?= urlencode($cat['slug']) ?><?= $searchParam ?>" class="filter-btn <?= $isActive ? 'active' : '' ?>">
            <?= htmlspecialchars($cat['name']) ?>
        </a>
    <?php endforeach; ?>
</div>

<div class="product-grid">
    <?php
    $conditions = [];
    $params = [];

    $baseQuery = "SELECT products.* FROM products";
    $joinCategory = false;

    if (isset($_GET['category']) && $_GET['category'] !== '') {
        $joinCategory = true;
        $conditions[] = "categories.slug = ?";
        $params[] = $_GET['category'];
    }

    if (isset($_GET['search']) && trim($_GET['search']) !== '') {
        $conditions[] = "products.name LIKE ?";
        $params[] = '%' . trim($_GET['search']) . '%';
    }

    if ($joinCategory) {
        $baseQuery .= " JOIN categories ON products.category_id = categories.id";
    }

    if (!empty($conditions)) {
        $baseQuery .= " WHERE " . implode(' AND ', $conditions);
    }

    $baseQuery .= " ORDER BY products.created_at DESC";

    $stmt = $pdo->prepare($baseQuery);
    $stmt->execute($params);
    $products = $stmt->fetchAll();

    if (count($products) === 0):
    ?>
        <p>No products found<?= isset($_GET['search']) && $_GET['search'] !== '' ? ' for "' . htmlspecialchars($_GET['search']) . '"' : '' ?>.</p>
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