<?php
session_start();
require_once "../dbconnect.php";

// login required
if (!isset($_SESSION["username"])) {
    header("Location: ../login.php");
    exit;
}

// Initialize cart if not set
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// ✅ Helper: Apply discount
function applyDiscount($basePrice, $discountType, $discountValue) {
    if ($discountType === 'percent' && $discountValue > 0) {
        return $basePrice - ($basePrice * ($discountValue / 100));
    } elseif ($discountType === 'fixed' && $discountValue > 0) {
        return max(0, $basePrice - $discountValue);
    }
    return $basePrice;
}

// ✅ Handle adding item to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lunchbox_id = intval($_POST['lunchbox_id']);
    $plan_id     = intval($_POST['plan_id']);
    $image       = $_POST['image'];

    // Fetch lunchbox
    $stmt = $conn->prepare("SELECT * FROM lunchboxes WHERE id = ?");
    $stmt->execute([$lunchbox_id]);
    $lunchbox = $stmt->fetch(PDO::FETCH_ASSOC);

    // Fetch plan
    $stmt = $conn->prepare("SELECT * FROM plans WHERE id = ?");
    $stmt->execute([$plan_id]);
    $plan = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$lunchbox || !$plan) {
        die("❌ Invalid lunchbox or plan.");
    }

    // ✅ Calculate price securely
    $price = $lunchbox['price'] * ($plan['duration_days'] / 30);
    $price = applyDiscount($price, $lunchbox['discount_type'], $lunchbox['discount_value']);
    $price = applyDiscount($price, $plan['discount_type'], $plan['discount_value']);

    // Check if item already exists in cart
    $found = false;
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['lunchbox_id'] == $lunchbox_id && $item['plan_id'] == $plan_id) {
            $item['quantity'] += 1;
            $found = true;
            break;
        }
    }
    unset($item);

    // If not found, add new
    if (!$found) {
        $_SESSION['cart'][] = [
            'lunchbox_id' => $lunchbox_id,
            'plan_id'     => $plan_id,
            'price'       => $price,
            'image'       => $image,
            'quantity'    => 1
        ];
    }
}

// Handle quantity update (+/-)
if (isset($_GET['action'], $_GET['index'])) {
    $index = intval($_GET['index']);
    if ($_GET['action'] === 'plus') {
        $_SESSION['cart'][$index]['quantity']++;
    } elseif ($_GET['action'] === 'minus') {
        $_SESSION['cart'][$index]['quantity']--;
        if ($_SESSION['cart'][$index]['quantity'] <= 0) {
            array_splice($_SESSION['cart'], $index, 1); // remove item
        }
    }
    header("Location: cart.php");
    exit;
}

//  Handle "Clear Cart"
if (isset($_GET['clear'])) {
    $_SESSION['cart'] = [];
    header("Location: cart.php");
    exit;
}

//  Handle "Remove"
if (isset($_GET['action'], $_GET['index'], $_GET['remove'])) {
    $index = intval($_GET['index']);
    if ($_GET['action'] === 'minus' && $_GET['remove'] == 1) {
        array_splice($_SESSION['cart'], $index, 1);
    }
    header("Location: cart.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Cart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
    <h2 class="mb-4">🛒 Your Cart</h2>

    <?php if (!empty($_SESSION['cart'])): ?>
        <table class="table table-bordered bg-white shadow-sm">
            <thead class="table-dark">
                <tr>
                    <th>Image</th>
                    <th>Lunchbox ID</th>
                    <th>Plan ID</th>
                    <th>Price (After Discount)</th>
                    <th>Quantity</th>
                    <th>Line Total</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php $total = 0; ?>
                <?php foreach ($_SESSION['cart'] as $index => $item): ?>
                <?php
                    // ✅ Fetch latest prices from DB
                    $stmt = $conn->prepare("SELECT price, discount_type, discount_value FROM lunchboxes WHERE id = ?");
                    $stmt->execute([$item['lunchbox_id']]);
                    $lunchbox = $stmt->fetch(PDO::FETCH_ASSOC);

                    $stmt = $conn->prepare("SELECT duration_days, discount_type, discount_value FROM plans WHERE id = ?");
                    $stmt->execute([$item['plan_id']]);
                    $plan = $stmt->fetch(PDO::FETCH_ASSOC);

                    // Base price before discount
                    $basePrice = $lunchbox['price'] * ($plan['duration_days'] / 30);

                    // Apply discounts
                    $discounted = applyDiscount($basePrice, $lunchbox['discount_type'], $lunchbox['discount_value']);
                    $discounted = applyDiscount($discounted, $plan['discount_type'], $plan['discount_value']);

                    // Line totals
                    $lineBaseTotal = $basePrice * $item['quantity'];
                    $lineDiscountTotal = $discounted * $item['quantity'];
                ?>
                <tr>
                    <td><img src="<?= htmlspecialchars($item['image']) ?>" width="80"></td>
                    <td><?= $item['lunchbox_id'] ?></td>
                    <td><?= $item['plan_id'] ?></td>
                    <td>
                        <s class="text-muted">$<?= number_format($basePrice, 2) ?></s><br>
                        <span class="text-success fw-bold">$<?= number_format($discounted, 2) ?></span>
                    </td>
                    <td>
                        <a href="cart.php?action=minus&index=<?= $index ?>" class="btn btn-sm btn-outline-danger">-</a>
                        <?= $item['quantity'] ?>
                        <a href="cart.php?action=plus&index=<?= $index ?>" class="btn btn-sm btn-outline-success">+</a>
                    </td>
                    <td>
                        <s class="text-muted">$<?= number_format($lineBaseTotal, 2) ?></s><br>
                        <span class="text-success fw-bold">$<?= number_format($lineDiscountTotal, 2) ?></span>
                    </td>
                    <td><a href="cart.php?action=minus&index=<?= $index ?>&remove=1" class="btn btn-sm btn-danger">Remove</a></td>
                </tr>
                <?php $total += $lineDiscountTotal; ?>
                <?php endforeach; ?>

            </tbody>
        </table>

        <h4 class="text-end">Total: <span class="text-success">$<?= number_format($total, 2) ?></span></h4>

        <div class="d-flex justify-content-between mt-4">
            <a href="cart.php?clear=1" class="btn btn-danger">Clear Cart</a>
            <a href="home.php" class="btn btn-secondary">Continue Shopping</a>
            <a href="checkout.php" class="btn btn-primary">Proceed to Checkout</a>
        </div>
    <?php else: ?>
        <p class="alert alert-info">Your cart is empty.</p>
        <a href="home.php" class="btn btn-secondary">Continue Shopping</a>
    <?php endif; ?>
</div>

</body>
</html>