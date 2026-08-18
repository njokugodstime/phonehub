<?php
require_once __DIR__ . '/auth-check.php';

if (!isset($_GET['id'])) {
    header('Location: manage-products.php');
    exit;
}

$productId = (int)$_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$productId]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: manage-products.php');
    exit;
}

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
        $update = $pdo->prepare("UPDATE products SET category_id = ?, name = ?, description = ?, price = ?, stock = ? WHERE id = ?");
        $update->execute([$categoryId ?: null, $name, $description, $price, $stock, $productId]);

        $success = true;

        // Refresh product data for the form
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$productId]);
        $product = $stmt->fetch();
    }
}

require_once __DIR__ . '/../includes/header.php';
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
?>

<h1>Edit Product</h1>

<?php if ($success): ?>
    <div class="success-box">
        <p>Product updated successfully!</p>
    </div>
<?php endif; ?>

<?php if (!empty($errors)): ?>
    <div class="error-box">
        <?php foreach ($errors as $err): ?>
            <p><?= htmlspecialchars($err) ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<form method="POST" class="admin-form">
    <label for="name">Product Name</label>
    <input type="text" name="name" id="name" value="<?= htmlspecialchars($product['name']) ?>" required>

    <label for="category_id">Category</label>
    <select name="category_id" id="category_id">
        <option value="">-- Select Category --</option>
        <?php foreach ($categories as $cat): ?>
            <option value="<?= (int)$cat['id'] ?>" <?= $product['category_id'] == $cat['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($cat['name']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label for="description">Description</label>
    <textarea name="description" id="description" rows="4"><?= htmlspecialchars($product['description']) ?></textarea>

    <label for="price">Price ($)</label>
    <input type="number" name="price" id="price" step="0.01" min="0" value="<?= htmlspecialchars($product['price']) ?>" required>

    <label for="stock">Stock Quantity</label>
    <input type="number" name="stock" id="stock" min="0" value="<?= htmlspecialchars($product['stock']) ?>" required>

    <button type="submit">Save Changes</button>
</form>

<a href="manage-products.php" class="back-link" style="margin-top: 15px; display: inline-block;">&larr; Back to Products</a>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>