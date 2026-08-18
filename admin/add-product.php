<?php
require_once __DIR__ . '/auth-check.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $categoryId = (int)($_POST['category_id'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $stock = (int)($_POST['stock'] ?? 0);

    if ($name === '') $errors[] = "Product name is required.";
    if ($price <= 0) $errors[] = "Price must be greater than 0.";
    if ($stock < 0) $errors[] = "Stock cannot be negative.";

    if (empty($errors)) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', $name), '-'));

        $stmt = $pdo->prepare("SELECT id FROM products WHERE slug = ?");
        $stmt->execute([$slug]);
        if ($stmt->fetch()) {
            $slug .= '-' . time();
        }

        $insert = $pdo->prepare("INSERT INTO products (category_id, name, slug, description, price, stock, image) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $insert->execute([$categoryId ?: null, $name, $slug, $description, $price, $stock, '']);

        $success = true;
    }
}

require_once __DIR__ . '/../includes/header.php';
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
?>

<h1>Add New Product</h1>

<?php if ($success): ?>
    <div class="success-box">
        <p>Product added successfully!</p>
        <a href="add-product.php">Add another</a> | <a href="dashboard.php">Back to Dashboard</a>
    </div>
<?php else: ?>

    <?php if (!empty($errors)): ?>
        <div class="error-box">
            <?php foreach ($errors as $err): ?>
                <p><?= htmlspecialchars($err) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="POST" class="admin-form">
        <label for="name">Product Name</label>
        <input type="text" name="name" id="name" required>

        <label for="category_id">Category</label>
        <select name="category_id" id="category_id">
            <option value="">-- Select Category --</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= (int)$cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="description">Description</label>
        <textarea name="description" id="description" rows="4"></textarea>

        <label for="price">Price ($)</label>
        <input type="number" name="price" id="price" step="0.01" min="0" required>

        <label for="stock">Stock Quantity</label>
        <input type="number" name="stock" id="stock" min="0" required>

        <button type="submit">Add Product</button>
    </form>

<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>