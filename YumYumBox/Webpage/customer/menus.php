<?php
session_start();
require_once "../dbconnect.php";
 // A centralized file for navbar logic

// login is optional here
$loggedIn = isset($_SESSION["username"]);

// Fetch all lunchboxes for navbar dropdown
$stmt = $conn->prepare("SELECT * FROM lunchboxes ORDER BY id DESC");
$stmt->execute();
$lunchboxes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get selected lunchbox
$lunchboxId = isset($_GET['lunchbox_id']) ? (int)$_GET['lunchbox_id'] : 0;

// If a lunchbox is selected -> fetch its menus
if ($lunchboxId > 0) {
    // Get lunchbox name
    $stmt = $conn->prepare("SELECT name FROM lunchboxes WHERE id = ?");
    $stmt->execute([$lunchboxId]);
    $lunchbox = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $conn->prepare("SELECT * FROM menus WHERE lunchbox_id = ? ORDER BY id DESC");
    $stmt->execute([$lunchboxId]);
    $menus = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    // No lunchbox selected -> show latest menus
    $lunchbox = ["name" => "All Lunchboxes"];
    $stmt = $conn->prepare("SELECT m.*, l.name AS lunchbox_name 
                                     FROM menus m
                                     JOIN lunchboxes l ON m.lunchbox_id = l.id
                                     ORDER BY m.id DESC");
    $stmt->execute();
    $menus = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch user data for navbar
$profilePic = "../productImage/default-avatar.png";
$cartCount = 0;
if ($loggedIn) {
    $username = $_SESSION["username"];
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user_id = $stmt->fetchColumn();

    $stmt = $conn->prepare("SELECT profile_image FROM profiles WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $userProfileImage = $stmt->fetchColumn();
    if ($userProfileImage) {
        $profilePic = "../" . $userProfileImage;
    }
    // Assumes cart items are in a session variable named 'cart'
    $cartCount = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($lunchbox['name']) ?> - Menus</title>
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
        background-color: #cc3300;
        border: none;
        color: #fff;
        transition: transform 0.2s ease-in-out, background-color 0.2s ease-in-out;
    }

    .btn-subscribe:hover,
    .btn-primary:hover {
        background-color: #993333;
        transform: scale(1.05);
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

    /* --- New & Improved Design Styles --- */

    .menu-card {
        border-radius: 15px;
        overflow: hidden;
        transition: box-shadow 0.3s ease-in-out, transform 0.3s ease-in-out;
    }

    .menu-card:hover {
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        transform: translateY(-5px);
    }

    .card-img-top {
        width: 100%;
        height: 250px;
        object-fit: contain; /* This is the key change to show the whole image */
        object-position: center;
        background-color: #ffffff; /* Optional: adds a white background to the empty space */
        border-bottom: 3px solid #cc3300;
    }

    .card-body {
        padding: 1.5rem;
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
                        <?php foreach ($lunchboxes as $lunchboxItem): ?>
                            <li><a class="dropdown-item" href="lunchbox.php?id=<?= $lunchboxItem['id'] ?>"><?= htmlspecialchars($lunchboxItem['name']) ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </li>
                <li class="nav-item dropdown d-none d-lg-block">
                    <a class="nav-link dropdown-toggle active" href="#" id="lunchboxDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Menus
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="lunchboxDropdown">
                        <?php foreach ($lunchboxes as $lunchboxItem): ?>
                            <li><a class="dropdown-item" href="menus.php?lunchbox_id=<?= $lunchboxItem['id'] ?>"><?= htmlspecialchars($lunchboxItem['name']) ?></a></li>
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
                            <?php foreach ($lunchboxes as $lunchboxItem): ?>
                                <li><a class="dropdown-item" href="lunchbox.php?id=<?= $lunchboxItem['id'] ?>"><?= htmlspecialchars($lunchboxItem['name']) ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                    <li class="nav-item dropdown d-lg-none">
                        <a class="nav-link dropdown-toggle active" href="#" id="lunchboxDropdownMobile2" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Menus
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="lunchboxDropdownMobile2">
                            <?php foreach ($lunchboxes as $lunchboxItem): ?>
                                <li><a class="dropdown-item" href="menus.php?lunchbox_id=<?= $lunchboxItem['id'] ?>"><?= htmlspecialchars($lunchboxItem['name']) ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                    <li class="nav-item d-lg-none"><a class="nav-link" href="aboutus.php">About Us</a></li>
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

<main class="container py-5">
    <h2 class="text-center mb-5"><?= htmlspecialchars($lunchbox['name']) ?> Menus</h2>
    <div class="row g-4">
        <?php if (count($menus) > 0): ?>
            <?php foreach ($menus as $menu): ?>
                <div class="col-md-4">
                    <div class="card menu-card h-100 shadow-sm">
                        <?php if (!empty($menu['image'])): ?>
                            <img src="../uploads/<?= htmlspecialchars($menu['image']) ?>" 
                                 alt="<?= htmlspecialchars($menu['name']) ?>" 
                                 class="card-img-top">
                        <?php else: ?>
                            <img src="../productImage/placeholder.jpg" alt="No Image" class="card-img-top">
                        <?php endif; ?>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?= htmlspecialchars($menu['name']) ?></h5>
                            <p class="card-text text-muted"><?= htmlspecialchars($menu['description']) ?></p>
                            <div class="mt-auto text-center">
                                <a href="lunchbox.php?id=<?= $menu['lunchbox_id'] ?>" class="btn btn-subscribe">
                                    Subscribe
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-center">No menus found for this lunchbox.</p>
        <?php endif; ?>
    </div>
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
                    <li><a href="#" class="text-white">Facebook</a></li>
                    <li><a href="#" class="text-white">Instagram</a></li>
                    <li><a href="#" class="text-white">Twitter</a></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="text-center p-3" style="background-color: #922b21;">
        © 2025 Lunchbox Co. All rights reserved.
    </div>
    
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>


</body>
</html>