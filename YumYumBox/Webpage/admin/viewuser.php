<?php
session_start();
require_once("../dbconnect.php");

if (!isset($_SESSION["username"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../login.php");
    exit;
}

// Handle role update
if (isset($_POST["updateRole"])) {
    $userId = $_POST["userId"];
    $newRole = $_POST["role"];

    try {
        $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->execute([$newRole, $userId]);

        $_SESSION['message'] = "✅ User role updated successfully!";
        header("Location: viewuser.php");
        exit;
    } catch (PDOException $e) {
        $_SESSION['message'] = "❌ Error updating role: " . $e->getMessage();
    }
}

// Fetch all users
$stmt = $conn->prepare("SELECT id, username, email, role FROM users ORDER BY id DESC");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Flash message
$message = "";
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Users - Admin Panel</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --red-dark: #993333;
            --red-medium: #cc3300;
            --cream: #f8f4ec;
            --white: #ffffff;
            --gray-dark: #333333;
            --gray-light: #eeeeee;
        }

        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--cream);
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 280px;
            background-color: var(--red-dark);
            color: var(--white);
            padding: 30px;
            position: fixed;
            height: 100%;
            display: flex;
            flex-direction: column;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }

        .sidebar h2 {
            text-align: center;
            margin-bottom: 40px;
            font-weight: 700;
            color: var(--cream);
            text-transform: uppercase;
            letter-spacing: 1px; /* ✅ same as dashboard */
        }

        .sidebar a {
            display: block;
            color: var(--white);
            text-decoration: none;
            padding: 15px 20px;
            margin-bottom: 10px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background-color: var(--red-medium);
            transform: translateX(5px);
        }

        .logout {
            margin-top: auto;
            padding-top: 20px;
            border-top: 1px solid rgba(255,255,255,0.1);
            text-align: center;
        }

        .logout a {
            color: var(--white);
            font-weight: 600;
            text-transform: uppercase;
        }

        .logout a:hover {
            color: var(--cream);
        }

        .main {
            margin-left: 280px;
            padding: 40px;
            width: calc(100% - 280px);
        }

        h1 {
            color: var(--red-dark);
            margin-bottom: 25px;
            font-weight: 700;
        }

        .message {
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-weight: 600;
            color: #155724;
            background: #d4edda;
            border: 1px solid #c3e6cb;
        }

        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: var(--white);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        th, td {
            padding: 15px 20px;
            border-bottom: 1px solid var(--gray-light);
            text-align: left;
        }

        th {
            background-color: var(--red-medium);
            color: var(--white);
            white-space: nowrap;
        }

        tr:hover {
            background-color: #f9f9f9;
        }

        .role-admin {
            color: green;
            font-weight: 600;
        }

        .role-customer {
            color: var(--gray-dark);
        }

        .role-form select {
            padding: 5px;
            border-radius: 6px;
            border: 1px solid #ccc;
            margin-right: 5px;
            font-size: 14px;
        }

        .role-form button {
            padding: 6px 12px;
            background: var(--red-medium);
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .role-form button:hover {
            background: var(--red-dark);
        }

        @media (max-width: 768px) {
            body {
                flex-direction: column;
            }

            .sidebar {
                position: static;
                width: 100%;
                height: auto;
                padding-bottom: 15px;
            }

            .main {
                margin-left: 0;
                width: 100%;
                padding: 20px;
            }

            th, td {
                padding: 10px;
                font-size: 14px;
            }

            .role-form button {
                padding: 5px 10px;
                font-size: 12px;
            }
        }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>YumYum Admin</h2>
    <a href="dashboard.php">📊 Dashboard</a>
    <a href="insertProduct.php">🍱 Manage Lunchboxes</a>
    <a href="viewUser.php" class="active">📋 View Users</a>
    <a href="viewProduct.php">📦 Lunchbox Reports</a>
    <a href="insertmenu.php">🧾 Insert Menus</a>
    <a href="viewmenu.php">📖 View Menus</a>
    <a href="insertPlans.php">📅 Insert Plans</a>
    <a href="adddiscounts.php">📊 Promotion</a>
    <a href="viewreview.php" >⭐️ View Reviews</a>
    <a href="admin_subscriptions.php" >📝 View Subscriptions</a>

    <div class="logout">
        <a href="../login.php">🚪 Logout</a>
    </div>
</div>

<div class="main">
    <h1>All Registered Users</h1>

    <?php if ($message): ?>
        <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user["id"]); ?></td>
                        <td><?= htmlspecialchars($user["username"]); ?></td>
                        <td><?= htmlspecialchars($user["email"]); ?></td>
                        <td class="role-<?= $user["role"]; ?>"><?= ucfirst($user["role"]); ?></td>
                        <td>
                            <form method="post" class="role-form" style="display:inline;">
                                <input type="hidden" name="userId" value="<?= $user["id"]; ?>">
                                <select name="role">
                                    <option value="customer" <?= $user["role"] === "customer" ? "selected" : "" ?>>Customer</option>
                                    <option value="admin" <?= $user["role"] === "admin" ? "selected" : "" ?>>Admin</option>
                                </select>
                                <button type="submit" name="updateRole">Update</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
