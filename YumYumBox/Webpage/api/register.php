<?php
// Set up error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once("../dbconnect.php");

// Set headers for CORS and to indicate a JSON response
header("Access-Control-Allow-Origin: http://localhost:5174");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

$response = ["success" => false, "message" => "An unknown error occurred."];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the raw JSON POST data sent from the React app
    $json_data = file_get_contents('php://input');
    $data = json_decode($json_data, true);

    $username = trim($data["username"] ?? '');
    $email = trim($data["email"] ?? '');
    $password = $data["password"] ?? '';

    // Password strength check (server-side)
    if (!preg_match("/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/", $password)) {
        $response = ["success" => false, "message" => "Password must be at least 8 characters long, contain at least one letter, one number, and one special character."];
    } else {
        try {
            // Check if username or email already exists
            $checkSql = "SELECT COUNT(*) FROM users WHERE username = ? OR email = ?";
            $checkStmt = $conn->prepare($checkSql);
            $checkStmt->execute([$username, $email]);
            if ($checkStmt->fetchColumn() > 0) {
                $response = ["success" => false, "message" => "Username or email already exists."];
            } else {
                // Hash password
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                // Insert into DB
                $sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$username, $email, $hashedPassword]);

                // Auto login (set session variables)
                $_SESSION["user_id"] = $conn->lastInsertId();
                $_SESSION["username"] = $username;
                $_SESSION["role"] = "customer";
                $_SESSION["profile_pic_url"] = 'https://placehold.co/100x100'; // Placeholder

                $response = [
                    "success" => true,
                    "message" => "Registration successful. You are now logged in.",
                    "user" => [
                        "id" => $_SESSION["user_id"],
                        "username" => $_SESSION["username"],
                        "role" => $_SESSION["role"],
                        "profile_pic_url" => $_SESSION["profile_pic_url"]
                    ]
                ];
            }
        } catch (PDOException $e) {
            $response = ["success" => false, "message" => "Registration failed: " . $e->getMessage()];
        }
    }
}

echo json_encode($response);
exit;
?>