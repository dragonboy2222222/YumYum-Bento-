<?php
session_start();
require_once "../dbconnect.php";

// login is optional here
$loggedIn = isset($_SESSION["username"]);
$profilePic = "../productImage/default-avatar.png";
$cartCount = 0;

if ($loggedIn) {
    $username = $_SESSION["username"];

    // Fetch user details from database
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user_id = $stmt->fetchColumn();

    // Fetch profile info for profile picture
    $stmt = $conn->prepare("SELECT profile_image FROM profiles WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $userProfileImage = $stmt->fetchColumn();
    if ($userProfileImage) {
        $profilePic = "../" . $userProfileImage;
    }
    // Get cart item count from session
    $cartCount = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
}

// Fetch all lunchboxes for navbar dropdown
$stmt = $conn->prepare("SELECT * FROM lunchboxes ORDER BY id DESC");
$stmt->execute();
$lunchboxes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Lunchbox Subscription</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Set Poppins as the default font for the body and headings */
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f4ec;
        }
        
        h1, h2, h3, h4, h5, h6, .navbar-brand {
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
        }
        
        /* Navbar */
        .navbar {
            background-color: #993333 !important;
        }
        .navbar .nav-link,
        .navbar .navbar-brand {
            color: #fff !important;
        }
        .navbar .nav-link:hover {
            color: #ffd9d9 !important; /* lighter red hover */
        }
        .profile-img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            cursor: pointer;
        }

        /* Subscribe button */
        .btn-subscribe,
        .btn-primary {
            background-color: #993333;
            border: none;
        }
        .btn-subscribe:hover,
        .btn-primary:hover {
            background-color: #993333; /* slightly darker */
        }

        /* Footer */
        footer {
            background-color: #993333 !important;
            color: #fff !important;
        }
        footer a {
            color: #fff !important;
            text-decoration: none;
        }
        footer a:hover {
            text-decoration: underline;
        }

        .about-section {
            background-color: #f8f4ec;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
    <div class="container-fluid">
        <div class="d-flex w-100 justify-content-between align-items-center">
            <ul class="navbar-nav d-flex flex-row align-items-center me-auto">
                <li class="nav-item">
                    <a class="navbar-brand" href="<?= $loggedIn ? 'home.php' : '../index.php' ?>">
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
                <li class="nav-item d-none d-lg-block"><a class="nav-link active" href="aboutus.php">About Us</a></li>
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
                    <li class="nav-item d-lg-none"><a class="nav-link active" href="aboutus.php">About Us</a></li>
                    <li class="nav-item d-lg-none"><a class="nav-link" href="reviews.php">Reviews</a></li>
                    <li class="nav-item d-lg-none"><a class="nav-link" href="faq.php">FAQ</a></li>
                    
                    <li class="nav-item">
                        <a class="nav-link position-relative" href="cart.php">
                            <i class="fas fa-shopping-cart"></i>
                            <?php if ($cartCount > 0): ?>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                    <?= $cartCount ?>
                                    <span class="visually-hidden">cart items</span>
                                </span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <?php if ($loggedIn): ?>
                        <li class="nav-item ms-3">
                            <a href="profile.php">
                                <img src="<?= htmlspecialchars($profilePic) ?>" alt="Profile" class="profile-img">
                            </a>
                        </li>
                        <li class="nav-item ms-3">
                            <a href="../logout.php" class="btn btn-outline-light">Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item ms-3">
                            <a href="../login.php" class="btn btn-outline-light">Login</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</nav>

<main>
    <section class="container-fluid about-section py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 mb-4 mb-md-0">
                    <h2 class="fw-bold mb-4" style="color:#993333;">Our Story</h2>
                    <p class="lead">
                        YumYumBento Co. was born from a simple idea: to bring the joy of wholesome, delicious meals to everyone's doorstep. We believe that good food is not just about sustenance; it's about culture, community, and care. Our journey began with a passion for supporting local, family-owned food businesses and a mission to make their culinary traditions accessible to you.
                    </p>
                    <p>
                        We meticulously curate our menus to offer a diverse range of flavors and experiences. Each meal is a tribute to authentic recipes, crafted with the freshest ingredients and packed with love. We're more than just a subscription service; we are a bridge connecting you to the rich tapestry of flavors from local kitchens.
                    </p>
                </div>
                <div class="col-md-6 text-center">
                    <img src="../productImage/teamm.jpg" alt="Our Story" class="img-fluid rounded shadow-lg">
                </div>
            </div>
        </div>
    </section>
    
    <section class="container py-5">
        <div class="row text-center">
            <div class="col-12">
                <h2 class="fw-bold mb-5">Meet Our Team</h2>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0">
                    <img src="../productImage/team1.jpg" class="card-img-top rounded-circle mx-auto" alt="Team Member 1" style="width: 150px; height: 150px; object-fit: cover;">
                    <div class="card-body">
                        <h5 class="card-title mt-3">Jane Okaza</h5>
                        <p class="card-text text-muted">Co-Founder & Head Chef</p>
                        <p>With over 20 years of experience, Jane brings her passion for traditional cooking to every menu.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0">
                    <img src="../productImage/team2.jpg" class="card-img-top rounded-circle mx-auto" alt="Team Member 2" style="width: 150px; height: 150px; object-fit: cover;">
                    <div class="card-body">
                        <h5 class="card-title mt-3">Shizumo Sanji</h5>
                        <p class="card-text text-muted">Co-Founder & Operations</p>
                        <p>Shizumo ensures our logistics run smoothly, so your delicious meals arrive fresh and on time.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0">
                    <img src="../productImage/team3.jpg" class="card-img-top rounded-circle mx-auto" alt="Team Member 3" style="width: 150px; height: 150px; object-fit: cover;">
                    <div class="card-body">
                        <h5 class="card-title mt-3">Kakuji Gojo</h5>
                        <p class="card-text text-muted">Community Manager</p>
                        <p>Kakuji is our voice and your main point of contact. He loves hearing your feedback and stories!</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<footer class="text-center text-lg-start mt-5" style="background-color: #993333; color: #fff;">
    <div class="container-fluid p-4">
        <div class="row">
            <div class="col-lg-6 col-md-12 mb-4">
                <h5 class="text-uppercase">YumYum Bento</h5>
                <p>Delivering healthy meals straight to your doorstep. Contact us for more info!</p>
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