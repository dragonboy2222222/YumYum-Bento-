<?php
session_start();
$error = "";

if (!isset($_SESSION['pending_user'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $otp_input = trim($_POST["otp"]);
    $pending = $_SESSION['pending_user'];

    if (time() > $pending['otp_expires']) {
        $error = "OTP expired. Please login again.";
        unset($_SESSION['pending_user']);
    } elseif ($otp_input == $pending['otp']) {
        // OTP correct, login success
        $_SESSION["user_id"] = $pending["id"];
        $_SESSION["username"] = $pending["username"];
        $_SESSION["role"] = $pending["role"];

        unset($_SESSION['pending_user']);

        // ✅ Redirect based on role
        if ($_SESSION["role"] === "admin") {
            header("Location: admin/dashboard.php");
        } else {
            header("Location: customer/home.php");
        }
        exit;
    } else {
        $error = "Invalid OTP.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify OTP</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --red-dark: #993333;
            --red-medium: #cc3300;
            --cream: #f8f4ec;
            --white: #ffffff;
            --gray-dark: #333333;
            --gray-light: #eeeeee;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--cream);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .verify-box {
            background: var(--white);
            padding: 30px 40px;
            border-radius: 12px;
            width: 350px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.15);
            text-align: center;
        }
        .verify-box h2 {
            margin-bottom: 20px;
            color: var(--red-dark);
            font-weight: 700;
        }
        .verify-box label {
            display: block;
            text-align: left;
            margin: 10px 0 5px;
            font-size: 14px;
            color: var(--gray-dark);
        }
        .verify-box input[type="text"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            margin-bottom: 15px;
            font-size: 14px;
            box-sizing: border-box;
            text-align: center;
            letter-spacing: 3px;
            font-weight: bold;
        }
        .verify-box button {
            width: 100%;
            background-color: var(--red-medium);
            color: var(--white);
            border: none;
            padding: 12px;
            font-size: 16px;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .verify-box button:hover {
            background-color: var(--red-dark);
        }
        .error {
            color: var(--red-medium);
            margin-bottom: 15px;
            font-size: 14px;
            font-weight: bold;
        }
        .note {
            margin-top: 10px;
            font-size: 13px;
            color: #777;
        }
    </style>
</head>
<body>
    <div class="verify-box">
        <h2>Verify OTP</h2>

        <?php if($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post">
            <label>Enter 6-digit Code:</label>
            <input type="text" name="otp" maxlength="6" required>
            <button type="submit">Verify</button>
        </form>

        <p class="note">Check your email for the verification code. Code expires in 5 minutes.</p>
    </div>
</body>
</html>