<?php
session_start();
require_once "../dbconnect.php";

// login is optional here
$loggedIn = isset($_SESSION["username"]);

// Check if lunchbox id is passed, otherwise use Classic Bento by default
$lunchbox_id = isset($_GET['id']) ? intval($_GET['id']) : 4; // fallback ID

// Fetch lunchbox details
$stmt = $conn->prepare("SELECT * FROM lunchboxes WHERE id = ?");
$stmt->execute([$lunchbox_id]);
$lunchbox = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$lunchbox) {
    die("❌ Lunchbox not found!");
}

// Fetch subscription plans
$plansStmt = $conn->query("SELECT * FROM plans ORDER BY id ASC");
$plans = $plansStmt->fetchAll(PDO::FETCH_ASSOC);

// helper to apply discount
function applyDiscount($basePrice, $discountType, $discountValue) {
    if ($discountType === 'percent' && $discountValue > 0) {
        return $basePrice - ($basePrice * ($discountValue / 100));
    } elseif ($discountType === 'amount' && $discountValue > 0) {
        // THIS IS THE CORRECTED LINE
        return max(0, $basePrice - $discountValue);
    }
    return $basePrice;
}

// Determine back button URL
$backUrl = $loggedIn ? 'home.php' : '../index.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($lunchbox['name']) ?> - Lunchbox</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --red-dark: #993333;
            --red-medium: #cc3300;
            --cream: #f8f4ec;
            --white: #ffffff;
            --gray-dark: #333333;
            --gray-light: #eeeeee;
        }
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--cream);
        }
        .container {
            max-width: 1000px;
        }
        .lunchbox-card {
            background: var(--white);
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-top: 40px;
        }
        .plan-card {
            border: 2px solid var(--gray-light);
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            background: var(--white);
            box-shadow: 0 5px 12px rgba(0,0,0,0.05);
            transition: 0.3s;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .plan-card:hover {
            transform: translateY(-5px);
            border-color: var(--red-medium);
        }
        .plan-card h4 {
            color: var(--red-dark);
            font-weight: 700;
        }
        .btn-subscribe {
            background-color: var(--red-medium);
            color: var(--white);
            border: none;
            padding: 10px 18px;
            border-radius: 8px;
            font-weight: 600;
            transition: 0.3s;
        }
        .btn-subscribe:hover {
            background-color: var(--red-dark);
        }
        .back-button-container {
            margin-top: 20px;
            text-align: right;
        }
        .back-button-container a {
            color: var(--red-dark);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s;
        }
        .back-button-container a:hover {
            color: var(--red-medium);
        }
        .price-text {
            color: var(--red-medium);
            font-weight: 700;
            font-size: 1.5rem;
        }
        .price-text .small-text {
            font-size: 1rem;
            color: #777;
        }
    </style>
</head>
<body>

<div class="container my-5">
    <div class="d-flex justify-content-end back-button-container">
        <a href="<?= htmlspecialchars($backUrl) ?>">
            ← Back
        </a>
    </div>

    <div class="lunchbox-card row align-items-center">
        <div class="col-md-6 text-center">
            <img src="<?= htmlspecialchars($lunchbox['image']) ?>" 
                 alt="<?= htmlspecialchars($lunchbox['name']) ?>" 
                 class="img-fluid rounded shadow-sm">
        </div>
        <div class="col-md-6">
            <h2 class="fw-bold" style="color:var(--red-dark);"><?= htmlspecialchars($lunchbox['name']) ?></h2>
            <p class="text-muted"><?= nl2br(htmlspecialchars($lunchbox['description'])) ?></p>
            <h4 class="fw-bold mb-4">
                $<?= number_format($lunchbox['price'], 2) ?>
                <?php if ($lunchbox['discount_value'] > 0): ?>
                    <span class="badge bg-success ms-2">
                        <?= $lunchbox['discount_type'] === 'percent' 
                            ? $lunchbox['discount_value'] . "% OFF" 
                            : "$" . $lunchbox['discount_value'] . " OFF" ?>
                    </span>
                <?php endif; ?>
            </h4>
        </div>
    </div>

    <h3 class="mt-5 text-center fw-bold" style="color:var(--red-dark);">Choose a Subscription Plan</h3>
    <div class="row mt-4">
        <?php foreach ($plans as $plan): ?>
            <?php
                // Base plan price
                $planPrice = $lunchbox['price'] * ($plan['duration_days'] / 30);

                // Apply lunchbox discount
                $planPrice = applyDiscount($planPrice, $lunchbox['discount_type'], $lunchbox['discount_value']);

                // Apply plan discount
                $planPrice = applyDiscount($planPrice, $plan['discount_type'], $plan['discount_value']);
            ?>
            <div class="col-md-4 mb-4">
                <div class="plan-card">
                    <div>
                        <h4><?= htmlspecialchars($plan['name']) ?></h4>
                        <p class="text-muted"><?= (int)$plan['duration_days'] ?> Days</p>
                    </div>
                    <div>
                        <h5 class="price-text">$<?= number_format($planPrice, 2) ?></h5>
                        <?php if ($plan['discount_value'] > 0): ?>
                            <p class="text-success fw-bold mb-2">
                                <?= $plan['discount_type'] === 'percent' 
                                    ? $plan['discount_value'] . "% OFF (Plan)" 
                                    : "$" . $plan['discount_value'] . " OFF (Plan)" ?>
                            </p>
                        <?php endif; ?>
                    </div>
                    <?php if (!$loggedIn): ?>
                        <button type="button" class="btn-subscribe mt-3" data-bs-toggle="modal" data-bs-target="#loginModal">
                            Subscribe
                        </button>
                    <?php else: ?>
                        <form action="cart.php" method="post" class="mt-3">
                            <input type="hidden" name="plan_id" value="<?= $plan['id'] ?>">
                            <input type="hidden" name="lunchbox_id" value="<?= $lunchbox['id'] ?>">
                            <input type="hidden" name="price" value="<?= $planPrice ?>">
                            <input type="hidden" name="image" value="<?= htmlspecialchars($lunchbox['image']) ?>">
                            <button type="submit" class="btn-subscribe">Subscribe</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="loginModalLabel" style="color: var(--red-dark); font-weight: 700;">Login Required</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                You need to login first before subscribing to a plan.
            </div>
            <div class="modal-footer">
                <a href="../login.php" class="btn btn-subscribe">Go to Login</a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<button id="chat-toggle" class="btn btn-danger rounded-circle position-fixed" 
        style="bottom:20px; right:20px; width:60px; height:60px; z-index:999;">
    💬
</button>

<div id="chat-box" class="card shadow position-fixed d-none" 
      style="bottom:90px; right:20px; width:300px; max-height:400px; z-index:999;">
    <div class="card-header bg-danger text-white">Chat with us</div>
    <div id="chat-messages" class="card-body overflow-auto" style="height:250px;"></div>
    
    <div id="predefined-questions" class="card-body overflow-auto" style="max-height: 150px;"></div>

    <form id="chat-form" class="card-footer d-flex">
        <input type="text" id="chat-input" class="form-control me-2" placeholder="Type a message..." />
        <button class="btn btn-danger" type="submit">Send</button>
    </form>
</div>

<script src="../assets/chatbot.js"></script>
</body>
</html>