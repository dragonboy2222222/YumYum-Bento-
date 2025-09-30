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

// --- PHP Logic for Reviews Page ---

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_review'])) {
    $rating = $_POST['rating'];
    $review_text = $_POST['review_text'];

    // Validate input
    if (empty($rating) || empty($review_text)) {
        $error = "Please provide a rating and a review.";
    } else {
        // Insert review into database
        $stmt = $conn->prepare("INSERT INTO reviews (user_id, rating, review_text) VALUES (?, ?, ?)");
        if ($stmt->execute([$user_id, $rating, $review_text])) {
            $success = "Thank you for your review!";
        } else {
            $error = "Failed to submit review. Please try again.";
        }
    }
}

// Fetch all reviews from the database, joining with the users and profiles tables to get user details
$stmt = $conn->prepare("
    SELECT r.*, u.username, p.profile_image
    FROM reviews r
    JOIN users u ON r.user_id = u.id
    LEFT JOIN profiles p ON u.id = p.user_id
    ORDER BY r.created_at DESC
");
$stmt->execute();
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Reviews</title>
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
        .review-card {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            padding: 20px;
        }
        .rating .fa-star {
            color: #ffc107;
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
                <li class="nav-item d-none d-lg-block"><a class="nav-link active" href="reviews.php">Reviews</a></li>
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
                    <li class="nav-item d-lg-none"><a class="nav-link active" href="reviews.php">Reviews</a></li>
                    
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
    <h2 class="text-center mb-5">Customer Reviews</h2>

    <div class="row justify-content-center mb-5">
        <div class="col-lg-8">
            <div class="review-card">
                <h4 class="mb-3">Write a Review</h4>
                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?= $success ?></div>
                <?php elseif (isset($error)): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                <form action="reviews.php" method="post">
                    <div class="mb-3">
                        <label for="rating" class="form-label">Rating</label>
                        <select class="form-select" id="rating" name="rating" required>
                            <option value="">Select a rating</option>
                            <option value="5">5 Stars</option>
                            <option value="4">4 Stars</option>
                            <option value="3">3 Stars</option>
                            <option value="2">2 Stars</option>
                            <option value="1">1 Star</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="review_text" class="form-label">Your Review</label>
                        <textarea class="form-control" id="review_text" name="review_text" rows="4" required></textarea>
                    </div>
                    <button type="submit" name="submit_review" class="btn btn-primary">Submit Review</button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <?php if (count($reviews) > 0): ?>
                <?php foreach ($reviews as $review): 
                    $user_profile_pic = "../productImage/default-avatar.png";
                    if (!empty($review['profile_image'])) {
                        $user_profile_pic = "../" . $review['profile_image'];
                    }
                ?>
                    <div class="review-card">
                        <div class="d-flex align-items-center mb-3">
                            <img src="<?= htmlspecialchars($user_profile_pic) ?>" alt="Profile" class="profile-img me-3">
                            <div>
                                <h5 class="mb-0"><?= htmlspecialchars($review['username']) ?></h5>
                                <small class="text-muted"><?= date('F j, Y', strtotime($review['created_at'])) ?></small>
                            </div>
                        </div>
                        <div class="rating mb-3">
                            <?php for ($i = 0; $i < 5; $i++): ?>
                                <?php if ($i < $review['rating']): ?>
                                    <i class="fas fa-star"></i>
                                <?php else: ?>
                                    <i class="far fa-star"></i>
                                <?php endif; ?>
                            <?php endfor; ?>
                        </div>
                        <p><?= nl2br(htmlspecialchars($review['review_text'])) ?></p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alert alert-info text-center">No reviews yet. Be the first to write one!</div>
            <?php endif; ?>
        </div>
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
        © 2025 YumYum Bento All rights reserved.
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