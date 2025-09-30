<?php
// Set up error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
// MAKE SURE TO UPDATE THIS PATH
require_once("../dbconnect.php"); 

require '../../PHPMailer-master/src/PHPMailer.php';
require '../../PHPMailer-master/src/SMTP.php';
require '../../PHPMailer-master/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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
    $password = $data["password"] ?? '';

    // Fetch user from database
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user["password"])) {
        // Generate a random 6-digit OTP
        $otp = rand(100000, 999999);

        // Store pending login information and OTP in the user's session
        $_SESSION["pending_user"] = [
            "id" => $user['id'],
            "username" => $user["username"],
            "role" => $user["role"],
            "email" => $user["email"],
            "otp" => $otp,
            "otp_expires" => time() + 300 // OTP expires in 5 minutes
        ];
        
        // --- PHPMailer Logic (Your Configuration) ---
        try {
            $mail = new PHPMailer(true);
            $mail->SMTPDebug = 0; 
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'theinpainghtun@gmail.com'; 
            $mail->Password   = 'absd pjmt nkil ghjm'; // App password
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;

            $mail->setFrom('theinpainghtun@gmail.com', 'Secure Login');
            $mail->addAddress($user["email"], $user["username"]);

            $mail->isHTML(true);
            $mail->Subject = 'Your Login Verification Code';
            $mail->Body    = "Hello <b>{$user['username']}</b>,<br><br>Your verification code is <b>$otp</b>.<br><br>This code expires in 5 minutes.";

            $mail->send();
            
            $response = ["success" => true, "message" => "OTP sent to your email. Please verify.", "otp_required" => true];

        } catch (Exception $e) {
            $response = ["success" => false, "message" => "Mailer Error: " . $mail->ErrorInfo];
        }
        
    } else {
        $response = ["success" => false, "message" => "Invalid username or password."];
    }
}

echo json_encode($response);
$conn = null;
exit;
?>
