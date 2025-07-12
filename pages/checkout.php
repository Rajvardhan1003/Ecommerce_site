<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include '../includes/db.php';

$user_id = $_SESSION['user_id'];

// Fetch cart items with product info
$stmt = $conn->prepare("
    SELECT p.id, p.name, p.price, p.image, c.quantity 
    FROM cart c 
    JOIN products p ON c.product_id = p.id 
    WHERE c.user_id = ?
");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate total
$total_cost = 0;
foreach ($cart_items as $item) {
    $total_cost += $item['price'] * $item['quantity'];
}

// Handle Order Placement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $address = $_POST['address'];

    // Insert into orders table
    $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, address, order_date) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$user_id, $total_cost, $address]);
    $order_id = $conn->lastInsertId();

    // Insert each item into order_items
    foreach ($cart_items as $item) {
        $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        $stmt->execute([$order_id, $item['id'], $item['quantity'], $item['price']]);
    }

    // Clear cart
    $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->execute([$user_id]);

    // Redirect or show confirmation
    header("Location: order_success.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Checkout</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: #f8f9fa;
            margin: 0;
        }
        .container {
            width: 90%;
            max-width: 800px;
            margin: 40px auto;
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h2 { text-align: center; }
        .total { font-size: 1.5em; margin-top: 20px; font-weight: bold; }
        textarea {
            width: 100%;
            height: 100px;
            margin-top: 15px;
            padding: 10px;
            border-radius: 5px;
        }
        button {
            padding: 12px 25px;
            background-color: #28a745;
            border: none;
            color: white;
            font-size: 1.1em;
            cursor: pointer;
            margin-top: 20px;
            border-radius: 5px;
        }
        button:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Checkout</h2>
    <?php if (empty($cart_items)): ?>
        <p>Your cart is empty!</p>
    <?php else: ?>
        <p><strong>Total Items:</strong> <?= count($cart_items); ?></p>
        <div class="total">Total Payable: ₹<?= number_format($total_cost, 2); ?></div>

        <form method="POST">
            <label for="address">Delivery Address:</label><br>
            <textarea name="address" required placeholder="Enter your delivery address..."></textarea><br>
            <button type="submit" name="place_order">Place Order</button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>
