<?php
session_start();
require_once "../dbconnect.php";

// ✅ Check if user is logged in
if (!isset($_SESSION["username"])) {
    header("Location: ../login.php");
    exit;
}

// ✅ Get username from session
$username = $_SESSION["username"];

// ✅ Fetch full user details from database
$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// ✅ If user not found, force logout
if (!$user) {
    session_destroy();
    header("Location: ../login.php");
    exit;
}

$user_id = $user["id"];
$role = $user["role"];

// ✅ Fetch profile info
$stmt = $conn->prepare("SELECT * FROM profiles WHERE user_id = ?");
$stmt->execute([$user_id]);
$profile = $stmt->fetch(PDO::FETCH_ASSOC);

// ✅ Profile picture (default if none)
$profilePic = "../productImage/default-avatar.png";
if ($profile && !empty($profile["profile_image"])) {
    $profilePic = "../" . $profile["profile_image"];
}

// ✅ Fetch lunchboxes
$stmt = $conn->prepare("SELECT * FROM lunchboxes ORDER BY id DESC");
$stmt->execute();
$lunchboxes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms and Conditions | YumYum Bento</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f4ec;
        }
        .navbar {
            background-color: #993333 !important;
        }
        .navbar-nav .nav-link,
        .navbar-brand {
            color: #fff !important;
        }
        .navbar-nav .nav-link:hover {
            color: #ffd9d9 !important;
        }
        .profile-img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            cursor: pointer;
        }
        h2, h3 {
            color: #993333;
            font-weight: 700;
        }
        .card {
            background-color: #fff;
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
    <div class="container-fluid">
        <div class="d-flex w-100 justify-content-between align-items-center">
            <ul class="navbar-nav d-flex flex-row align-items-center me-auto">
                <li class="nav-item">
                    <a class="navbar-brand" href="home.php">
                        <img src="../productImage/loogo.png" alt="Logo" width="280" class="d-inline-block align-text-top">
                    </a>
                </li>
                
                <li class="nav-item dropdown d-none d-lg-block">
                    <a class="nav-link dropdown-toggle" href="#" id="lunchboxDropdown2" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        LunchBoxes
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="lunchboxDropdown2">
                        <?php foreach ($lunchboxes as $lunchbox): ?>
                            <li><a class="dropdown-item" href="lunchbox.php?id=<?= $lunchbox['id'] ?>"><?= htmlspecialchars($lunchbox['name']) ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </li>
                <li class="nav-item dropdown d-none d-lg-block">
                    <a class="nav-link dropdown-toggle" href="#" id="lunchboxDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Menus
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="lunchboxDropdown">
                        <?php foreach ($lunchboxes as $lunchbox): ?>
                            <li><a class="dropdown-item" href="menus.php?lunchbox_id=<?= $lunchbox['id'] ?>"><?= htmlspecialchars($lunchbox['name']) ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </li>
                <li class="nav-item d-none d-lg-block"><a class="nav-link" href="aboutus.php">About Us</a></li>
                <li class="nav-item d-none d-lg-block"><a class="nav-link" href="reviews.php">Reviews</a></li>
                <li class="nav-item d-none d-lg-block"><a class="nav-link" href="faq.php">FAQ</a></li>
            </ul>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMenu" aria-controls="navbarMenu" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarMenu">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item dropdown d-lg-none">
                        <a class="nav-link dropdown-toggle" href="#" id="lunchboxDropdownMobile" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            LunchBoxes
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="lunchboxDropdownMobile">
                            <?php foreach ($lunchboxes as $lunchbox): ?>
                                <li><a class="dropdown-item" href="lunchbox.php?id=<?= $lunchbox['id'] ?>"><?= htmlspecialchars($lunchbox['name']) ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                    <li class="nav-item dropdown d-lg-none">
                        <a class="nav-link dropdown-toggle" href="#" id="lunchboxDropdownMobile2" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Menus
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="lunchboxDropdownMobile2">
                            <?php foreach ($lunchboxes as $lunchbox): ?>
                                <li><a class="dropdown-item" href="menus.php?lunchbox_id=<?= $lunchbox['id'] ?>"><?= htmlspecialchars($lunchbox['name']) ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                    <li class="nav-item d-lg-none"><a class="nav-link" href="aboutus.php">About Us</a></li>
                    <li class="nav-item d-lg-none"><a class="nav-link" href="reviews.php">Reviews</a></li>
                    <li class="nav-item d-lg-none"><a class="nav-link" href="faq.php">FAQ</a></li>
                    
                    <li class="nav-item"><a class="nav-link" href="cart.php">Cart</a></li>
                    <li class="nav-item ms-3">
                        <a href="profile.php">
                            <img src="<?= htmlspecialchars($profilePic) ?>" alt="Profile" class="profile-img">
                        </a>
                    </li>
                    <li class="nav-item ms-3">
                        <a href="../logout.php" class="btn btn-outline-light">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>

<main class="container py-5">
    <div class="card p-4 p-md-5">
        <h2 class="text-center mb-4">Terms and Conditions</h2>
        <p class="text-muted text-center mb-5">Last updated: September 16, 2025</p>

        <section class="mb-5">
            <h3>1. Acceptance of Terms</h3>
            <p>By accessing or using the services provided by YumYum Bento., you agree to be bound by these Terms and Conditions. If you do not agree to these terms, please do not use our services.</p>
        </section>

        <section class="mb-5">
            <h3>2. Subscription and Payments</h3>
            <ul>
                <li><strong>2.1. Subscription Plans:</strong> We offer various subscription plans for our meal delivery service. By subscribing, you agree to pay the recurring fees associated with your chosen plan.</li>
                <li><strong>2.2. No Cancellations or Refunds:</strong> Due to our commitment to providing fresh, high-quality meals, we purchase ingredients and plan our production based on subscriptions. Therefore, once a payment is made, **the order cannot be cancelled and no refunds will be provided.** All sales are final.</li>
                <li><strong>2.3. Payment Information:</strong> You agree to provide current, complete, and accurate payment information. You authorize us to charge your payment method for all subscription fees and any applicable taxes.</li>
            </ul>
        </section>
        
        <section class="mb-5">
            <h3>3. Delivery Policy</h3>
            <ul>
                <li><strong>3.1. Delivery Schedule:</strong> We will deliver your lunchboxes on the scheduled days as per your subscription plan. Delivery times may vary depending on traffic and other conditions.</li>
                <li><strong>3.2. Missed Delivery:</strong> In the event that our company is unable to deliver your meal on a scheduled day due to unforeseen circumstances on our end (e.g., driver issues, vehicle breakdown), we will add one extra day to your subscription period to compensate for the missed meal.</li>
                <li><strong>3.3. Customer Responsibility:</strong> It is your responsibility to ensure the delivery address is accurate and that someone is available to receive the delivery. We are not responsible for spoiled food or theft if the delivery is left unattended for an extended period.</li>
            </ul>
        </section>

        <section class="mb-5">
            <h3>4. Termination and Refund Policy</h3>
            <ul>
                <li><strong>4.1. Termination by User:</strong> You can choose not to renew your subscription at the end of your billing cycle. However, no refunds will be provided for any unused portion of the current subscription period.</li>
                <li><strong>4.2. Termination by Company:</strong> We reserve the right to terminate or suspend your subscription at our discretion, without prior notice. This may occur if you violate these terms or for any other reason deemed necessary.</li>
                <li><strong>4.3. Company Inability to Deliver:</strong> If YumYum Bento becomes unable to continue providing service to you (e.g., due to business closure, ceasing operations in your area), we will issue a full or prorated refund for any prepaid, undelivered meals. This is the only circumstance in which a refund will be provided.</li>
            </ul>
        </section>

        <section class="mb-5">
            <h3>5. User Conduct</h3>
            <p>You agree to use our services for lawful purposes only and not to engage in any conduct that could damage, disable, or impair our website or services. This includes providing false or misleading information.</p>
        </section>

        <section class="mb-5">
            <h3>6. Limitation of Liability</h3>
            <p>YumYum Bento will not be liable for any indirect, incidental, special, or consequential damages arising from your use of our services or any products purchased through the site. Our total liability to you will not exceed the amount you paid for your subscription in the last one month.</p>
        </section>

        <section class="mb-5">
            <h3>7. Intellectual Property</h3>
            <p>All content on this website, including text, graphics, logos, and images, is the property of YumYum Bento or its content suppliers and is protected by intellectual property laws. You may not use, reproduce, or distribute any content without our express written permission.</p>
        </section>
        
        <section class="mb-5">
            <h3>8. Governing Law</h3>
            <p>These Terms and Conditions are governed by the laws of the country in which YumYum Bento operates. Any disputes shall be resolved in the courts of that jurisdiction.</p>
        </section>
        
        <section>
            <h3>9. Changes to Terms</h3>
            <p>We reserve the right to modify these terms at any time. We will notify you of any changes by posting the new terms on this page. Your continued use of the service after such changes constitutes your acceptance of the new terms.</p>
        </section>
        
        <p class="mt-5 text-center text-muted">For any questions about these terms, please contact us at support@lunchboxco.com.</p>
    </div>
</main>

<footer class="text-center text-lg-start mt-5" style="background-color: #993333; color: #fff;">
    <div class="container-fluid p-4">
        <div class="row">
            <div class="col-lg-6 col-md-12 mb-4">
                <h5 class="text-uppercase">YumYum Bento</h5>
                <p style="color: #fff;">Delivering healthy meals straight to your doorstep. Contact us for more info!</p>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <h6 class="text-uppercase">Links</h6>
                <ul class="list-unstyled mb-0">
                    <li><a href="term.php" class="text-white">Terms and Conditions</a></li>
                    <li><a href="policy.php" class="text-white">Privacy Policy</a></li>
                    <li><a href="aboutus.php" class="text-white">About Us</a></li>
                    <li><a href="faq.php" class="text-white">FAQ</a></li>
                </ul>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <h6 class="text-uppercase">Follow Us</h6>
                <ul class="list-unstyled mb-0">
                      <li><a href="https://www.facebook.com/yumyumbentos/" class="text-white">Facebook</a></li>
                    <li><a href="https://www.instagram.com/explore/locations/104070141120529/yumyum-bento/recent/" class="text-white">Instagram</a></li>
                    <li><a href="https://twitter.com/yumyumbento/status/781263054356885504" class="text-white">Twitter</a></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="text-center p-3" style="background-color: #922b21;">
        © 2025 YumYum Bento. All rights reserved.
    </div>
</footer>

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
>
</body>
</html>