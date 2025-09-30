<?php
session_start();
require_once "../dbconnect.php";


// login is optional here
$loggedIn = isset($_SESSION["username"]);


// Check if lunchbox id is passed
if (!isset($_GET['id'])) {
    die("❌ No lunchbox selected!");
}

$id = intval($_GET['id']);

// Fetch lunchbox details
$stmt = $conn->prepare("SELECT * FROM lunchboxes WHERE id = ?");
$stmt->execute([$id]);
$lunchbox = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$lunchbox) {
    die("❌ Lunchbox not found!");
}

// Fetch subscription plans
$plansStmt = $conn->query("SELECT * FROM plans ORDER BY id ASC");
$plans = $plansStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($lunchbox['name']) ?> - Lunchbox</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f8f4ec;
    }
    .lunchbox-card {
      background: #fff;
      padding: 25px;
      border-radius: 12px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
      margin-top: 40px;
    }
    .plan-card {
      border: 1px solid #eee;
      border-radius: 12px;
      padding: 20px;
      text-align: center;
      background: #fff;
      box-shadow: 0 5px 12px rgba(0,0,0,0.05);
      transition: 0.3s;
    }
    .plan-card:hover {
      transform: translateY(-5px);
      border-color: #cc3300;
    }
    .plan-card h4 {
      color: #993333;
      font-weight: 700;
    }
    .btn-subscribe {
      background-color: #cc3300;
      color: #fff;
      border: none;
      padding: 10px 18px;
      border-radius: 8px;
      font-weight: 600;
      transition: 0.3s;
    }
    .btn-subscribe:hover {
      background-color: #993333;
    }
  </style>
</head>
<body>

<div class="container">
  <div class="lunchbox-card row align-items-center">
    <div class="col-md-6 text-center">
      <img src="<?= htmlspecialchars($lunchbox['image']) ?>" 
           alt="<?= htmlspecialchars($lunchbox['name']) ?>" 
           class="img-fluid rounded">
    </div>
    <div class="col-md-6">
      <h2 class="fw-bold" style="color:#993333;"><?= htmlspecialchars($lunchbox['name']) ?></h2>
      <p class="text-muted"><?= nl2br(htmlspecialchars($lunchbox['description'])) ?></p>
      <h4 class="fw-bold mb-4">$<?= number_format($lunchbox['price'], 2) ?></h4>
    </div>
  </div>

  <h3 class="mt-5 text-center fw-bold" style="color:#993333;">Choose a Subscription Plan</h3>
  <div class="row mt-4">
  <?php foreach ($plans as $plan): ?>
    <?php
      // Algorithm: scale price based on duration
      $planPrice = $lunchbox['price'] * ($plan['duration_days'] / 30);
    ?>
    <div class="col-md-4 mb-4">
      <div class="plan-card">
        <h4><?= htmlspecialchars($plan['name']) ?></h4>
        <p class="text-muted"><?= (int)$plan['duration_days'] ?> Days</p>
        <h5 class="fw-bold" style="color:#cc3300;">$<?= number_format($planPrice, 2) ?></h5>
      <?php if (!isset($_SESSION["username"])): ?>
  <!-- If user is NOT logged in -->
  <button type="button" class="btn-subscribe" data-bs-toggle="modal" data-bs-target="#loginModal">
    Subscribe
  </button>
<?php else: ?>
  <!-- If user IS logged in -->
  <form action="cart.php" method="post">
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

<!-- Login Required Modal -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="loginModalLabel">Login Required</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        You need to login first before subscribing to a plan.
      </div>
      <div class="modal-footer">
        <a href="../login.php" class="btn btn-primary">Go to Login</a>
      </div>
    </div>
  </div>
</div>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>


</body>
</html>
