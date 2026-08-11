<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../includes/db.php';

// Redirect if cart is empty
if (empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit;
}

// Get cart items and total
$ids = array_keys($_SESSION['cart']);
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
$stmt->execute($ids);
$products = $stmt->fetchAll();

$cartItems = [];
$total = 0;
foreach ($products as $product) {
    $qty = $_SESSION['cart'][$product['id']];
    $subtotal = $product['price'] * $qty;
    $total += $subtotal;
    $cartItems[] = ['product' => $product, 'quantity' => $qty, 'subtotal' => $subtotal];
}

$errors = [];
$success = false;

// Handle order placement
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if ($name === '') $errors[] = "Name is required.";
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "A valid email is required.";

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            // Find or create a guest user for this order
            $userStmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $userStmt->execute([$email]);
            $user = $userStmt->fetch();

            if ($user) {
                $userId = $user['id'];
            } else {
                $insertUser = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'customer')");
                $insertUser->execute([$name, $email, password_hash(uniqid(), PASSWORD_DEFAULT)]);
                $userId = $pdo->lastInsertId();
            }

            // Create the order
            $orderStmt = $pdo->prepare("INSERT INTO orders (user_id, total, status) VALUES (?, ?, 'pending')");
            $orderStmt->execute([$userId, $total]);
            $orderId = $pdo->lastInsertId();

            // Insert order items
            $itemStmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            foreach ($cartItems as $item) {
                $itemStmt->execute([
                    $orderId,
                    $item['product']['id'],
                    $item['quantity'],
                    $item['product']['price']
                ]);
            }

            $pdo->commit();
            unset($_SESSION['cart']);
            $success = true;
            $completedOrderId = $orderId;

        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = "Something went wrong placing your order. Please try again.";
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<h1>Checkout</h1>

<?php if ($success): ?>
    <div class="success-box">
        <h2>Order placed successfully!</h2>
        <p>Your order #<?= (int)$completedOrderId ?> has been received.</p>
        <a href="index.php">Return to Home</a>
    </div>
<?php else: ?>

    <?php if (!empty($errors)): ?>
        <div class="error-box">
            <?php foreach ($errors as $err): ?>
                <p><?= htmlspecialchars($err) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <table class="cart-table">
        <thead>
            <tr><th>Product</th><th>Qty</th><th>Subtotal</th></tr>
        </thead>
        <tbody>
            <?php foreach ($cartItems as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['product']['name']) ?></td>
                    <td><?= (int)$item['quantity'] ?></td>
                    <td>$<?= number_format($item['subtotal'], 2) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <p class="cart-total">Total: $<?= number_format($total, 2) ?></p>

    <form method="POST" class="checkout-form">
        <label for="name">Full Name</label>
        <input type="text" name="name" id="name" required>

        <label for="email">Email</label>
        <input type="email" name="email" id="email" required>

        <button type="submit">Place Order</button>
    </form>

<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>