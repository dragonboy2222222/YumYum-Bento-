<?php
session_start();
require_once("../dbconnect.php");

// Redirect if user not logged in
if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit;
}
$user_id = $_SESSION['user_id']; // This line will now work correctly

// Redirect if cart is empty
if (empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit;
}

// ✅ Recalculate total using cart
$total = 0;
foreach ($_SESSION['cart'] as $item) {
    $total += $item['price'] * $item['quantity'];
}

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $method = $_POST['method'];
    $address = !empty($_POST['address']) ? $_POST['address'] : null;

    // Set status: cash = pending, others = paid
    $status = ($method === 'cash') ? 'pending' : 'paid';

    try {
        $conn->beginTransaction();

        // 1. Insert checkout
        $stmt = $conn->prepare("
            INSERT INTO checkouts (user_id, total_amount, status)
            VALUES (:user_id, :total_amount, :status)
        ");
        $stmt->execute([
            ':user_id'     => $user_id,
            ':total_amount'=> $total,
            ':status'      => $status
        ]);
        $checkoutId = $conn->lastInsertId();

        // 2. Insert subscriptions for each cart item
        foreach ($_SESSION['cart'] as $item) {
            for ($i = 0; $i < $item['quantity']; $i++) {
                $stmt = $conn->prepare("
                    INSERT INTO subscriptions 
                        (user_id, lunchbox_id, plan_id, start_date, end_date, status, checkout_id, address)
                    VALUES 
                        (:user_id, :lunchbox_id, :plan_id, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY), 'active', :checkout_id, :address)
                ");
                $stmt->execute([
                    ':user_id'     => $user_id,
                    ':lunchbox_id' => $item['lunchbox_id'],
                    ':plan_id'     => $item['plan_id'],
                    ':checkout_id' => $checkoutId,
                    ':address'     => $address
                ]);
            }
        }

        // 3. Insert single payment for this checkout
        $stmt = $conn->prepare("
            INSERT INTO payments (user_id, checkout_id, amount, method, status, transaction_id, paid_at)
            VALUES (:user_id, :checkout_id, :amount, :method, :status, :txid, NOW())
        ");
        $stmt->execute([
            ':user_id'     => $user_id,
            ':checkout_id' => $checkoutId,
            ':amount'      => $total,
            ':method'      => $method,
            ':status'      => $status,
            ':txid'        => uniqid("TXN")
        ]);

        $conn->commit();

        // Clear cart
        $_SESSION['cart'] = [];

        echo "<div style='padding:20px; font-family:sans-serif;'>
                  <h2>✅ Thanks, your order is confirmed!</h2>
                  <p>Checkout ID: <b>" . htmlspecialchars($checkoutId) . "</b></p>
                  <p>Total Amount: <b>$" . number_format($total, 2) . "</b></p>
                  <p>Payment Method: <b>" . htmlspecialchars($method) . "</b></p>
                  <p>Delivery Address: <b>" . htmlspecialchars($address) . "</b></p>
                  <a href='home.php'>Go back to home</a>
              </div>";
        exit;

    } catch (Exception $e) {
        $conn->rollBack();
        die("Checkout failed: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Checkout</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
    <h2 class="mb-4">Checkout</h2>

    <form method="post" class="bg-white p-4 shadow-sm rounded">
        <h4>Order Summary</h4>
        <ul class="list-group mb-3">
            <?php foreach ($_SESSION['cart'] as $item): ?>
                <li class="list-group-item d-flex justify-content-between">
                    <span>Lunchbox <?= htmlspecialchars($item['lunchbox_id']) ?> (Plan <?= htmlspecialchars($item['plan_id']) ?>) × <?= htmlspecialchars($item['quantity']) ?></span>
                    <strong>$<?= number_format($item['price'] * $item['quantity'], 2) ?></strong>
                </li>
            <?php endforeach; ?>
            <li class="list-group-item d-flex justify-content-between bg-light">
                <span>Total</span>
                <strong>$<?= number_format($total, 2) ?></strong>
            </li>
        </ul>

        <div class="mb-3">
            <label class="form-label">Payment Method</label>
            <select name="method" class="form-select" required>
                <option value="credit_card">Credit Card</option>
                <option value="paypal">PayPal</option>
                <option value="bank_transfer">Bank Transfer</option>
                <option value="cash">Cash</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Delivery Address</label>
            <textarea class="form-control" name="address" rows="3" required></textarea>
        </div>

        <button type="submit" class="btn btn-primary w-100">Confirm & Pay</button>
    </form>
</div>

</body>
</html>