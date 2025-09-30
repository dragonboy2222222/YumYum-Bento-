<?php
// Start a session and handle CORS
session_start();
header("Access-Control-Allow-Origin: http://localhost:5174");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With, Cookie");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once("../dbconnect.php");

// Check login
if (!isset($_SESSION["username"])) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Unauthorized. Please log in."]);
    exit;
}

$response = ["success" => false, "message" => "An unknown error occurred."];

// Get current user from DB
$sql = "SELECT id FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$_SESSION["username"]]);
$user = $stmt->fetch();
$user_id = $user["id"];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Handle profile creation/update
    $full_name = trim($_POST["full_name"] ?? '');
    $phone = trim($_POST["phone"] ?? '');
    $address = trim($_POST["address"] ?? '');

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

    try {
        // Check if profile exists
        $check = $conn->prepare("SELECT * FROM profiles WHERE user_id = ?");
        $check->execute([$user_id]);
        $existing = $check->fetch();

        if ($existing) {
            // Update profile
            $sql = "UPDATE profiles SET full_name=?, phone=?, address=?, profile_image=IFNULL(?, profile_image) WHERE user_id=?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$full_name, $phone, $address, $profile_image, $user_id]);
        } else {
            // Create new profile
            $sql = "INSERT INTO profiles (user_id, full_name, phone, address, profile_image) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$user_id, $full_name, $phone, $address, $profile_image]);
        }

        $response = ["success" => true, "message" => "Profile updated successfully."];

    } catch (PDOException $e) {
        http_response_code(500);
        $response = ["success" => false, "message" => "Database error: " . $e->getMessage()];
    }

} elseif ($_SERVER["REQUEST_METHOD"] == "GET") {
    // Handle fetching user profile and subscriptions
    try {
        // Fetch user and profile data
        $sql = "SELECT u.username, u.email, p.full_name, p.phone, p.address, p.profile_image FROM users u LEFT JOIN profiles p ON u.id = p.user_id WHERE u.id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$user_id]);
        $profileData = $stmt->fetch(PDO::FETCH_ASSOC);

        // Fetch user subscriptions
        $sql_subs = "SELECT s.*, b.name AS lunchbox_name, b.price AS lunchbox_price FROM subscriptions s JOIN lunchboxes b ON s.lunchbox_id = b.id WHERE s.user_id = ? ORDER BY s.start_date DESC";
        $stmt_subs = $conn->prepare($sql_subs);
        $stmt_subs->execute([$user_id]);
        $subscriptions = $stmt_subs->fetchAll(PDO::FETCH_ASSOC);

        $response = [
            "success" => true,
            "user" => $profileData,
            "subscriptions" => $subscriptions
        ];
    } catch (PDOException $e) {
        http_response_code(500);
        $response = ["success" => false, "message" => "Database error: " . $e->getMessage()];
    }
}

echo json_encode($response);
exit;
?>