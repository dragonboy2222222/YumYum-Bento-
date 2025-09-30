<?php
session_start();
require_once("../dbconnect.php");

// Check login
if (!isset($_SESSION["username"])) {
    header("Location: ../login.php");
    exit;
}

// Get current user from DB
$sql = "SELECT * FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$_SESSION["username"]]);
$user = $stmt->fetch();

if (!$user) {
    die("User not found.");
}

$user_id = $user["id"];

// Handle profile form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = trim($_POST["full_name"]);
    $phone = trim($_POST["phone"]);
    $address = trim($_POST["address"]);

    // Upload profile image
    $profile_image = null;
    if (!empty($_FILES["profile_image"]["name"])) {
        $targetDir = "../uploads/";
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        $fileName = time() . "_" . basename($_FILES["profile_image"]["name"]);
        $targetFile = $targetDir . $fileName;

        if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $targetFile)) {
            $profile_image = "uploads/" . $fileName;
        }
    }

    // Check if profile exists
    $check = $conn->prepare("SELECT * FROM profiles WHERE user_id = ?");
    $check->execute([$user_id]);
    $existing = $check->fetch();

    if ($existing) {
        // Update profile
        $sql = "UPDATE profiles SET full_name=?, phone=?, address=?, 
                 profile_image=IFNULL(?, profile_image), updated_at=NOW() WHERE user_id=?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$full_name, $phone, $address, $profile_image, $user_id]);
    } else {
        // Create new profile
        $sql = "INSERT INTO profiles (user_id, full_name, phone, address, profile_image) 
                 VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$user_id, $full_name, $phone, $address, $profile_image]);
    }

    // After creation, redirect to home.php
    header("Location: home.php");
    exit;
}

// Fetch user profile
$stmt = $conn->prepare("SELECT * FROM profiles WHERE user_id = ?");
$stmt->execute([$user_id]);
$profile = $stmt->fetch();

// Fetch user subscriptions
$stmt = $conn->prepare("SELECT s.*, b.name AS lunchbox_name, b.price AS lunchbox_price
                        FROM subscriptions s
                        JOIN lunchboxes b ON s.lunchbox_id = b.id
                        WHERE s.user_id = ?
                        ORDER BY s.start_date DESC"); // Changed 'subscribed_date' to 'start_date'
$stmt->execute([$user_id]);
$subscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html>
<head>
    <title>User Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f4ec;
        }
        .profile-container {
            max-width: 800px;
        }
        .profile-header {
            background-color: #993333;
            color: #fff;
            padding: 2rem;
            border-top-left-radius: 0.5rem;
            border-top-right-radius: 0.5rem;
        }
        .profile-image {
            width: 150px;
            height: 150px;
            border: 5px solid #fff;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .profile-details {
            padding: 2rem;
        }
        .section-title {
            color: #993333;
            border-bottom: 2px solid #ccc;
            padding-bottom: 0.5rem;
            margin-bottom: 1.5rem;
        }
        .card-subscriptions .card-header {
            background-color: #cc3300;
            color: #fff;
            font-weight: bold;
        }
        .btn-back {
            background-color: #993333;
            color: #fff;
            border: none;
        }
        .btn-back:hover {
            background-color: #7a2828;
            color: #fff;
        }
    </style>
</head>
<body class="bg-light">

<div class="container mt-5 profile-container">
    <div class="card shadow">
        <div class="profile-header text-center">
            <h2 class="fw-bold mb-0">My Profile</h2>
        </div>

        <div class="card-body p-4 profile-details">
            <?php if (!$profile): ?>
                <h3 class="section-title">Create Your Profile</h3>
                <form method="post" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="full_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Profile Image</label>
                        <input type="file" name="profile_image" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-primary">Create Profile</button>
                </form>
            <?php else: ?>
                <div class="row align-items-center">
                    <div class="col-md-4 text-center">
                        <?php if ($profile["profile_image"]): ?>
                            <img src="../<?php echo htmlspecialchars($profile["profile_image"]); ?>" 
                                 alt="Profile" class="rounded-circle mb-3 profile-image">
                        <?php else: ?>
                            <img src="https://via.placeholder.com/150" alt="Profile Placeholder" class="rounded-circle mb-3 profile-image">
                        <?php endif; ?>
                        <h4 class="fw-bold"><?php echo htmlspecialchars($profile["full_name"]); ?></h4>
                        <a href="editprofile.php" class="btn btn-warning mt-2">Edit Profile</a>
                    </div>
                    <div class="col-md-8">
                        <h3 class="section-title">Contact Information</h3>
                        <p><strong>Username:</strong> <?php echo htmlspecialchars($user["username"]); ?></p>
                        <p><strong>Phone:</strong> <?php echo htmlspecialchars($profile["phone"]); ?></p>
                        <p><strong>Address:</strong> <?php echo htmlspecialchars($profile["address"]); ?></p>
                    </div>
                </div>

                <hr class="my-4">

                <div class="card card-subscriptions mt-4">
                    <div class="card-header">
                        My Subscriptions
                    </div>
                    <ul class="list-group list-group-flush">
                        <?php if (count($subscriptions) > 0): ?>
                            <?php foreach ($subscriptions as $sub): ?>
                                <li class="list-group-item">
                                    <h5 class="fw-bold"><?php echo htmlspecialchars($sub['lunchbox_name']); ?></h5>
                                    <p class="mb-1"><strong>Price:</strong> $<?php echo htmlspecialchars($sub['lunchbox_price']); ?></p>
                                    <p class="mb-1"><strong>Status:</strong> <span class="badge bg-success"><?php echo htmlspecialchars($sub['status']); ?></span></p>
                                    <p class="mb-1"><strong>Subscribed Date:</strong> <?php echo date('F j, Y', strtotime($sub['start_date'])); ?></p>
                                    <?php if ($sub['status'] == 'active'): ?>
                                        <p class="mb-0 text-success fw-bold">Active!</p>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="list-group-item text-center text-muted">No subscriptions found.</li>
                        <?php endif; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="text-center mt-4">
                <a href="home.php" class="btn btn-back">Back to Home</a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>