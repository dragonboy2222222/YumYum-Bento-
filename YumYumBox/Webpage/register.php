<?php
session_start();
require_once("dbconnect.php");

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    // Password strength check (server-side)
    if (!preg_match("/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/", $password)) {
        $message = "Password must be at least 8 characters long, contain at least one letter, one number, and one special character.";
    } else {
        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        try {
            // Insert into DB
            $sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$username, $email, $hashedPassword]);

            // ✅ Fetch the new user's ID
            $user_id = $conn->lastInsertId();

            // ✅ Auto login (set session variables)
            $_SESSION["user_id"] = $user_id;
            $_SESSION["username"] = $username;
            $_SESSION["role"] = "customer"; // default role (adjust if needed)

            // ✅ Redirect to profile page
            header("Location: customer/profile.php");
            exit;

        } catch (PDOException $e) {
            $message = "Registration failed: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <style>
        /* Define new color variables */
        :root {
            --red-dark: #993333;
            --red-medium: #cc3300;
            --cream: #f8f4ec;
            --white: #ffffff;
            --gray-dark: #333333;
            --gray-light: #eeeeee;
        }

        body {
            background-color: var(--cream);
            font-family: 'Poppins', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }

        .register-box {
            background-color: var(--white);
            padding: 30px 40px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            width: 380px;
        }

        h2 {
            text-align: center;
            color: var(--red-dark);
            font-weight: 700;
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            color: var(--gray-dark);
            font-size: 14px;
        }

        input, select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            margin-bottom: 15px;
            font-size: 14px;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            background-color: var(--red-medium);
            color: var(--white);
            border: none;
            padding: 12px;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: var(--red-dark);
        }

        .message {
            text-align: center;
            margin-top: 15px;
            color: var(--red-medium);
            font-weight: bold;
        }

        .link-container {
            margin-top: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .login-link, .back-link {
            font-size: 14px;
            display: block;
        }

        .login-link a, .back-link a {
            color: var(--red-dark);
            text-decoration: none;
            font-weight: bold;
        }

        .login-link a:hover, .back-link a:hover {
            text-decoration: underline;
        }

        .password-strength {
            color: var(--red-medium);
            font-size: 12px;
            margin-top: 5px;
        }
    </style>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>

    <div class="register-box">
        <h2>Register New User</h2>

        <form method="post" action="">
            <label>Username:</label>
            <input type="text" name="username" required>

            <label>Email:</label>
            <input type="email" name="email">

            <label>Password:</label>
            <input type="password" name="password" id="password" required>
            <div id="password-strength-message" class="password-strength"></div>

            <button type="submit">Register</button>
        </form>

        <?php if ($message): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="link-container">
            <div class="back-link">
                <a href="index.php">← Back</a>
            </div>
            <div class="login-link">
                Already have an account? <a href="login.php">Login here</a>
            </div>
        </div>
    </div>

    <script>
        const passwordInput = document.getElementById('password');
        const passwordStrengthMessage = document.getElementById('password-strength-message');

        passwordInput.addEventListener('input', function() {
            const password = passwordInput.value;

            // Password Strength Pattern (min 8 chars, 1 letter, 1 number, 1 special char)
            const strongPasswordPattern = /^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;

            if (!password) {
                passwordStrengthMessage.textContent = '';
            } else if (!strongPasswordPattern.test(password)) {
                passwordStrengthMessage.textContent = 'Password must be at least 8 characters long, contain at least one letter, one number, and one special character.';
            } else {
                passwordStrengthMessage.textContent = 'Strong password';
                passwordStrengthMessage.style.color = 'green';
            }
        });
    </script>

</body>
</html>
