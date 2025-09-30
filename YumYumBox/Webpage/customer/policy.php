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
    <title>Privacy Policy | YumYum Bento</title>
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
                    
                    <li class="nav-item">
                        <a class="nav-link position-relative" href="cart.php">
                            <i class="fas fa-shopping-cart"></i>
                            <?php 
                                $cartCount = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
                                if ($cartCount > 0):
                            ?>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                    <?= $cartCount ?>
                                    <span class="visually-hidden">cart items</span>
                                </span>
                            <?php endif; ?>
                        </a>
                    </li>
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
        <h2 class="text-center mb-4">Privacy Policy</h2>
        <p class="text-muted text-center mb-5">Last updated: September 16, 2025</p>

        <section class="mb-5">
            <h3>1. Information We Collect</h3>
            <p>We collect personal information that you voluntarily provide to us when you register on the site, place an order, or contact us. This information may include your name, email address, phone number, shipping address, and payment details.</p>
        </section>

        <section class="mb-5">
            <h3>2. How We Use Your Information</h3>
            <p>We use the information we collect to:</p>
            <ul>
                <li>Process and fulfill your orders and subscriptions.</li>
                <li>Communicate with you about your account, orders, and promotions.</li>
                <li>Improve our website and services.</li>
                <li>Process payments and prevent fraudulent transactions.</li>
                <li>Provide customer support.</li>
            </ul>
        </section>
        
        <section class="mb-5">
            <h3>3. Data Protection</h3>
            <p>We implement a variety of security measures to maintain the safety of your personal information. Your personal data is stored in secure networks and is only accessible by a limited number of persons who have special access rights to such systems and are required to keep the information confidential.</p>
            <p>All payment transactions are processed through a gateway provider and are not stored or processed on our servers.</p>
        </section>

        <section class="mb-5">
            <h3>4. Sharing Your Information</h3>
            <p>We do not sell, trade, or otherwise transfer your personally identifiable information to outside parties without your consent, except to trusted third parties who assist us in operating our website, conducting our business, or servicing you, as long as those parties agree to keep this information confidential.</p>
        </section>
        
        <section class="mb-5">
            <h3>5. Cookies</h3>
            <p>We use cookies to enhance your experience. Cookies are small files that a site or its service provider transfers to your computer's hard drive through your web browser (if you allow) that enables the site's or service provider's systems to recognize your browser and capture and remember certain information.</p>
        </section>
        
        <section>
            <h3>6. Your Consent</h3>
            <p>By using our site, you consent to our privacy policy.</p>
        </section>
        
        <p class="mt-5 text-center text-muted">For any questions about this policy, please contact us at support@lunchboxco.com.</p>
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
</body>
</html>