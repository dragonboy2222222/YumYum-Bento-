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
    <title>Lunchbox Subscription</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
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
        .image-container {
            height: 500px;
            overflow: hidden;
        }
        .image-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.2s ease-in-out; /* Faster transition */
        }
        .image-container:hover img {
            transform: scale(1.05);
        }
        h2 {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 20px;
            color: #333;
        }
        p {
            font-size: 1.2rem;
            line-height: 1.6;
            color: #555;
        }
        section {
            padding: 40px;
            background-color: #f8f4ec;
        }
        .btn {
            background-color: #cc3300;
            border: none;
            color: white;
            padding: 15px 25px;
            font-size: 1rem;
            font-weight: bold;
            border-radius: 5px;
            cursor: pointer;
            transition: transform 0.2s ease-in-out, background-color 0.2s ease-in-out; /* Faster transition */
        }
        .btn:hover {
            background-color: #993333;
            transform: scale(1.05);
        }
        /* Section for Featured By */
        .section-featured-by {
            background-color: #5a1f1f;
            color: #fff;
            padding: 50px 0;
            position: relative;
            overflow: hidden;
        }
        .featured-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            position: absolute;
            top: 0;
            left: 0;
        }
        .text-overlay {
            position: relative;
            z-index: 1;
            text-align: center;
            color: white;
        }
        .brand-text {
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0;
            padding: 0.5rem;
            text-transform: uppercase;
            color: #fff; /* Changed to white as requested */
        }
        @media (max-width: 768px) {
            .brand-text {
                font-size: 1.2rem;
            }
        }
        /* Image hover animation for the 'How it works' and 'First Lunchbox' sections */
        .img-fluid.rounded {
            transition: transform 0.2s ease-in-out; /* Faster transition */
        }
        .img-fluid.rounded:hover {
            transform: scale(1.03);
        }
        .predefined-questions-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: 8px; /* Spacing between buttons */
            max-height: 150px;
            overflow-y: auto;
        }

        .predefined-q {
            font-size: 0.85rem; /* Smaller font size */
            padding: 6px 10px; /* Smaller padding */
            white-space: nowrap; /* Prevents text from wrapping */
        }
        .value-section {
    background-color: #922b21;
    color: #fff;
    padding: 80px 0;
    text-align: center;
}

.value-card {
    background: rgba(255, 255, 255, 0.1); /* Slightly more opaque for better contrast */
    backdrop-filter: blur(8px); /* Increased blur for smoother effect */
    border: 1px solid rgba(255, 255, 255, 0.3); /* Slightly stronger border */
    border-radius: 15px; /* Slightly more rounded */
    padding: 40px 20px;
    height: 100%;
    transition: transform 0.3s cubic-bezier(0.25, 0.8, 0.25, 1), background 0.3s cubic-bezier(0.25, 0.8, 0.25, 1), box-shadow 0.3s cubic-bezier(0.25, 0.8, 0.25, 1); /* Faster, smoother transitions with a cubic-bezier function */
    position: relative; /* Needed for pseudo-element animation */
    overflow: hidden; /* Ensures glow stays within bounds */
}

.value-card:hover {
    transform: translateY(-20px) scale(1.02); /* More pronounced lift and slight scale */
    background: rgba(255, 255, 255, 0.2); /* More prominent background on hover */
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3); /* Stronger shadow */
}

/* Pseudo-element for a subtle glow/shine effect on hover */
.value-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1) 20%, rgba(255, 255, 255, 0.3) 50%, rgba(255, 255, 255, 0.1) 80%, transparent);
    transition: left 0.7s cubic-bezier(0.25, 0.8, 0.25, 1);
}

.value-card:hover::before {
    left: 100%;
}


.value-card h5 {
    color: #fff; /* Keeping white for strong contrast against the darker background */
    font-weight: 700;
    letter-spacing: 1.5px; /* Slightly increased letter spacing for readability */
    text-transform: uppercase;
    text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.4); /* Subtle text shadow for better definition */
}

.value-card p {
    color: #e0e0e0; /* Slightly off-white for body text for softer appearance */
    transition: color 0.3s ease;
}

.value-card:hover p {
    color: #fff; /* White on hover for more emphasis */
}

.value-icon {
    font-size: 4rem; /* Larger icon */
    color: #ffda47; /* Brighter gold/yellow */
    margin-bottom: 25px; /* More space below icon */
    text-shadow: 0 0 15px rgba(255, 218, 71, 0.7); /* More prominent glow effect */
    transition: transform 0.3s ease-in-out, color 0.3s ease-in-out; /* Add transition for icon */
}

.value-card:hover .value-icon {
    transform: rotateY(15deg) scale(1.1); /* Slight 3D rotation and scale on hover */
    color: #ffe89e; /* Even brighter gold on hover */
}

.value-line {
    width: 70px; /* Slightly shorter default line */
    height: 4px; /* Thicker line */
    background-color: #ffda47; /* Brighter gold */
    border: none;
    opacity: 0.9;
    margin: 0 auto 25px auto;
    transition: width 0.3s cubic-bezier(0.25, 0.8, 0.25, 1); /* Faster, smoother transition for line */
}

.value-card:hover .value-line {
    width: 120px; /* More significant expansion on hover */
    background-color: #ffe89e; /* Brighter line on hover */
}

/* Ensure the main heading is also white for consistency */
.value-section h2 {
    color: #fff !important;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5); /* Stronger shadow for main heading */
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
                    <a class="nav-link dropdown-toggle active" href="#" id="lunchboxDropdown2" role="button" data-bs-toggle="dropdown" aria-expanded="false">
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
                        <a class="nav-link dropdown-toggle active" href="#" id="lunchboxDropdownMobile" role="button" data-bs-toggle="dropdown" aria-expanded="false">
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
                    <li class="nav-item d-lg-none"><a class="nav-link" href="#">Reviews</a></li>
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




<main class="container-fluid px-0">

<section class="row align-items-center g-0" style="background-color:#f8f4ec;">
    <div class="col-md-6 image-container">
        <img src="../productImage/fam.webp" alt="Our Team" class="img-fluid object-fit-cover">
    </div>
    <div class="col-md-6 p-5 d-flex flex-column justify-content-center">
        <h6 class="text-uppercase fw-bold mb-3" style="color:#993333;">What Makes Us Different</h6>
        <h2 class="fw-bold mb-4">Our Commitment to Craft & Culture!</h2>
        <p class="mb-4">
            With a Lunchbox subscription, enjoy delicious meals while supporting small family-run businesses and preserving culinary traditions.
        </p>
        <a href="lunchbox.php" class="btn btn-primary btn-lg">
            Subscribe Now
        </a>
    </div>
</section>


    <section class="value-section">
        <div class="container">
            <h2 class="mb-5 fw-bold" style="color: #fff;">Our Values: What We Stand For</h2>
            <div class="row g-5">
                <div class="col-md-4">
                    <div class="value-card">
                        <i class="fas fa-hand-holding-heart value-icon"></i>
                        <hr class="value-line">
                        <h5 class="fw-bold mt-4">Community Focus</h5>
                        <p>We partner with local, family-owned businesses to bring you authentic, high-quality meals while supporting our community.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="value-card">
                        <i class="fas fa-seedling value-icon"></i>
                        <hr class="value-line">
                        <h5 class="fw-bold mt-4">Freshness First</h5>
                        <p>We are dedicated to using the freshest, seasonal ingredients to ensure every meal is nutritious, delicious, and a delight to eat.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="value-card">
                        <i class="fas fa-utensils value-icon"></i>
                        <hr class="value-line">
                        <h5 class="fw-bold mt-4">Culinary Tradition</h5>
                        <p>Our meals celebrate time-honored recipes and cultural traditions, offering a unique and flavorful experience with every lunchbox.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>



<section class="container py-5">
    <h2 class="text-center mb-5">How it works</h2>
    <div class="row text-center g-4">
        <div class="col-md-4">
            <img src="../productImage/yumyum1.jpg" alt="Subscribe" class="img-fluid mb-3 rounded">
            <h5 class="fw-bold">SUBSCRIBE</h5>
            <p class="text-muted">
                Choose your preferred lunchbox category and subscription plan (30, 60, or 90 days) with just a few clicks.
            </p>
        </div>
        <div class="col-md-4">
            <img src="../productImage/yumyum2.jpg" alt="Receive" class="img-fluid mb-3 rounded">
            <h5 class="fw-bold">RECEIVE</h5>
            <p class="text-muted">
                Freshly prepared lunchboxes are delivered straight to your doorstep on your selected schedule.
            </p>
        </div>
        <div class="col-md-4">
            <img src="../productImage/yumyum3.png" alt="Enjoy" class="img-fluid mb-3 rounded">
            <h5 class="fw-bold">ENJOY</h5>
            <p class="text-muted">
                Enjoy balanced meals with dynamic menus, and manage your subscriptions easily through your profile.
            </p>
        </div>
    </div>
</section>

<section class="container py-5">
    <div class="row align-items-center g-4">
        <div class="col-md-6 text-center">
            <img src="../productImage/yumyum4.png" alt="Lunchbox" class="img-fluid rounded">
        </div>
        <div class="col-md-6">
            <h6 class="text-uppercase fw-bold mb-3" style="color:#993333;">Your First Lunchbox Includes:</h6>
            <h2 class="fw-bold mb-4">Fresh and Delicious Meals Delivered to You!</h2>
            <ul class="list-unstyled mb-4">
                <li class="mb-2">🍱 <strong>Variety of Lunchboxes</strong> – Choose from six different lunchbox categories designed to fit different tastes and lifestyles.</li>
                <li class="mb-2">🥗 <strong>Fresh Menus</strong> – Each lunchbox comes with dynamic menus that change regularly for a balanced and exciting meal plan.</li>
                <li class="mb-2">📅 <strong>Flexible Plans</strong> – Select from 30, 60, or 90-day subscription plans to match your schedule and preferences.</li>
                <li class="mb-2">💳 <strong>Easy Subscription</strong> – Simple checkout with multiple payment options for a smooth ordering experience.</li>
                <li class="mb-2">🛍️ <strong>Convenient Delivery</strong> – Your lunchbox is delivered straight to your door on your chosen schedule.</li>

            </ul>
            <a href="lunchbox.php" class="btn btn-primary btn-lg">
                SUBSCRIBE NOW
            </a>
        </div>
    </div>
</section>


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
        © 2025 Lunchbox Co. All rights reserved.
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