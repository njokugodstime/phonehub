<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PhoneHub - Phones & Accessories</title>
    <link rel="stylesheet" href="/phonehub/public/css/style.css">
</head>
<body>
    <header class="site-header">
        <div class="logo">PhoneHub</div>
        <nav>
            <a href="/phonehub/public/index.php">Home</a>
            <a href="/phonehub/public/products.php">Products</a>
            <a href="/phonehub/public/cart.php">Cart</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <span>Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?></span>
                <a href="/phonehub/public/logout.php">Logout</a>
            <?php else: ?>
                <a href="/phonehub/public/login.php">Login</a>
                <a href="/phonehub/public/register.php">Register</a>
            <?php endif; ?>
        </nav>
    </header>
    <main>