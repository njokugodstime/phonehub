<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../includes/db.php';

// Handle adding item to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $productId = (int)$_POST['product_id'];
    $quantity = max(1, (int)$_POST['quantity']);

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId] += $quantity;
    } else {
        $_SESSION['cart'][$productId] = $quantity;
    }

    header('Location: cart.php');
    exit;
}

// Handle removing item from cart
if (isset($_GET['remove'])) {
    $removeId = (int)$_GET['remove'];
    unset($_SESSION['cart'][$removeId]);
    header('Location: cart.php');
    exit;
}

require_once __DIR__ . '/../includes/header.php';

$cartItems = [];
$total = 0;

if (!empty($_SESSION['cart'])) {
    $ids = array_keys($_SESSION['cart']);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
    $stmt->execute($ids);
    $products = $stmt->fetchAll();

    foreach ($products as $product) {
        $qty = $_SESSION['cart'][$product['id']];
        $subtotal = $product['price'] * $qty;
        $total += $subtotal;
        $cartItems[] = [
            'product' => $product,
            'quantity' => $qty,
            'subtotal' => $subtotal
        ];
    }
}
?>

<h1>Your Cart</h1>

<?php if (empty($cartItems)): ?>
    <p>Your cart is empty. <a href="products.php">Browse products</a></p>
<?php else: ?>
    <table class="cart-table">
        <thead>
            <tr>
                <th>Product</th>
                <th>Price</th>
                <th>Qty</th>
                <th>Subtotal</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($cartItems as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['product']['name']) ?></td>
                    <td>$<?= number_format($item['product']['price'], 2) ?></td>
                    <td><?= (int)$item['quantity'] ?></td>
                    <td>$<?= number_format($item['subtotal'], 2) ?></td>
                    <td><a href="cart.php?remove=<?= (int)$item['product']['id'] ?>" class="remove-link">Remove</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <p class="cart-total">Total: $<?= number_format($total, 2) ?></p>
    <a href="checkout.php" class="checkout-btn">Proceed to Checkout</a>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>