<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once("dbconnect.php"); 

require '../PHPMailer-master/src/PHPMailer.php';
require '../PHPMailer-master/src/SMTP.php';
require '../PHPMailer-master/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    // Fetch user from database
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user["password"])) {
        // Generate OTP
        $otp = rand(100000, 999999);

        // Save pending login info in session
        $_SESSION["pending_user"] = [
            "id" => $user['id'],
            "username" => $user["username"],
            "role" => $user["role"],
            "otp" => $otp,
            "otp_expires" => time() + 300
        ];

        // Send OTP via PHPMailer
        $mail = new PHPMailer(true);
        try {
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

            header("Location: verify.php");
            exit;

        } catch (Exception $e) {
            $error = "Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        $error = "Invalid username or password.";
    }
}
?>


           <!-- Commend the up backend and activate the backend from downside if the user can't login with otp -->



<?php
/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once("dbconnect.php"); 

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    // Fetch user from database
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user["password"])) {
        // Login successful, store user info in session
        $_SESSION["username"] = $user["username"];
        $_SESSION["id"] = $user["id"];
        $_SESSION["role"] = $user["role"];

        // Role-based redirect
        if ($user["role"] === "admin") {
            header("Location: admin/dashboard.php");
        } else if ($user["role"] === "user") {
            header("Location: customer/homepage.php");
        } else {
            // fallback redirect
            header("Location: index.php");
        }
        exit;
    } else {
        $error = "Invalid username or password.";
    }
}
*/
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f4ec; /* Cream background */
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-box {
            background: #fff;
            padding: 30px 40px;
            border-radius: 12px;
            width: 350px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.15);
            text-align: center;
        }
        .login-box h2 {
            margin-bottom: 20px;
            color: #993333; /* Dark red for headers */
            font-weight: 700;
        }
        .login-box label {
            display: block;
            text-align: left;
            margin: 10px 0 5px;
            font-size: 14px;
            color: #555;
        }
        .login-box input[type="text"],
        .login-box input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            margin-bottom: 15px;
            font-size: 14px;
            box-sizing: border-box;
        }
        .login-box button {
            width: 100%;
            background-color: #cc3300; /* Medium red for buttons */
            color: #fff;
            border: none;
            padding: 12px;
            font-size: 16px;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .login-box button:hover {
            background-color: #993333; /* Dark red on hover */
        }
        .error {
            color: #cc3300; /* Medium red for errors */
            margin-bottom: 15px;
            font-size: 14px;
            font-weight: bold;
        }
        .link-container {
            margin-top: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .register-link, .back-link {
            font-size: 14px;
            display: block;
        }
        .register-link a, .back-link a {
            color: #993333; /* Dark red for links */
            text-decoration: none;
            font-weight: bold;
        }
        .register-link a:hover, .back-link a:hover {
            text-decoration: underline;
        }
    </style>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="login-box">
        <h2>User Login</h2>

        <?php if($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post">
            <label>Username:</label>
            <input type="text" name="username" required>

            <label>Password:</label>
            <input type="password" name="password" required>

            <button type="submit">Login</button>
        </form>

        <div class="link-container">
            <div class="back-link">
                <a href="index.php">← Back</a>
            </div>
            <div class="register-link">
                Don’t have an account? <a href="register.php">Register here</a>
            </div>
        </div>
    </div>
</body>
</html>