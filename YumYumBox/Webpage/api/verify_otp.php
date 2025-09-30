<?php
// customer-react/api/verify_otp_api.php

// Set up error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

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

    $otp_input = trim($data["otp"] ?? '');
    $pending = $_SESSION['pending_user'] ?? null;

    if (!$pending) {
        http_response_code(401);
        $response = ["success" => false, "message" => "No pending login session found. Please login again."];
    } elseif (time() > $pending['otp_expires']) {
        http_response_code(400);
        $response = ["success" => false, "message" => "OTP expired. Please login again."];
        // Clean up the expired session data
        unset($_SESSION['pending_user']);
    } elseif ($otp_input == $pending['otp']) {
        
        // OTP correct, finalize login
        
        // *****************************************
        // *** CRITICAL FIX: Changed "id" to "user_id" ***
        // *****************************************
        $_SESSION["user_id"] = $pending["id"]; 
        
        $_SESSION["username"] = $pending["username"];
        $_SESSION["role"] = $pending["role"];
        // Placeholder for profile pic
        $_SESSION["profile_pic_url"] = 'https://placehold.co/100x100'; 

        // Clear the pending user session, as they are now logged in
        unset($_SESSION['pending_user']);

        // Return a JSON response with user info
        http_response_code(200);
        $response = [
            "success" => true,
            "message" => "Login successful.",
            "user" => [
                // Use "user_id" from the session for the response payload "id" field
                "id" => $_SESSION["user_id"], 
                "username" => $_SESSION["username"],
                "role" => $_SESSION["role"],
                "profile_pic_url" => $_SESSION["profile_pic_url"]
            ]
        ];

    } else {
        // Invalid OTP
        http_response_code(400);
        $response = ["success" => false, "message" => "Invalid OTP."];
    }
}

echo json_encode($response);
exit; // End the script to prevent any further output
?>