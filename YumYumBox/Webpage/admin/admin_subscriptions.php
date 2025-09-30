<?php
session_start();
require_once("../dbconnect.php");

// Check if the user is an admin
if (!isset($_SESSION["username"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../login.php");
    exit;
}

// Handle deactivation request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['deactivate_id'])) {
    $subscription_id = $_POST['deactivate_id'];
    
    // Update the status of the subscription to 'deactive'
    $sql = "UPDATE subscriptions SET status = 'deactive' WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$subscription_id]);
    
    // Redirect to prevent form resubmission
    header("Location: admin_subscriptions.php");
    exit;
}

// Fetch all subscriptions with user and lunchbox details
$sql = "SELECT s.id, s.end_date, s.status, u.username, b.name AS lunchbox_name
        FROM subscriptions s
        JOIN users u ON s.user_id = u.id
        JOIN lunchboxes b ON s.lunchbox_id = b.id
        ORDER BY s.end_date ASC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$subscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Subscriptions</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
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

        * {
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
            
            letter-spacing: 1px; /* ✅ same as dashboard */
        }

        .sidebar a {
            display: block;
            color: var(--white);
            text-decoration: none;
            padding: 15px 20px;
            margin-bottom: 10px;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-weight: 600;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background-color: var(--red-medium);
            color: var(--white);
            transform: translateX(5px);
        }

        .logout {
            margin-top: auto;
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid rgba(255,255,255,0.1);
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

        .main h1 {
            color: var(--red-dark);
            margin-bottom: 25px;
            font-weight: 700;
        }
        
        /* Specific styles for the content table */
        .table thead {
            background-color: var(--red-dark);
            color: var(--white);
        }
        .table tbody tr:nth-child(odd) {
            background-color: #f3e9d3;
        }
        .btn-deactivate {
            background-color: var(--red-medium);
            border-color: var(--red-medium);
            color: var(--white);
            transition: background-color 0.2s;
        }
        .btn-deactivate:hover {
            background-color: var(--red-dark);
            border-color: var(--red-dark);
            color: var(--white);
        }
        .status-active {
            color: green;
            font-weight: bold;
        }
        .status-deactive {
            color: red;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>YumYum</h2>
    <a href="dashboard.php">📊 Dashboard</a>
    <a href="insertProduct.php">🍱 Manage Lunchboxes</a>
    <a href="viewUser.php">📋 View Users</a>
    <a href="viewProduct.php">📦 Lunchbox Reports</a>
    <a href="insertmenu.php">🧾 Insert Menus</a>
    <a href="viewmenu.php">📖 View Menus</a>
    <a href="insertPlans.php">📅 Insert Plans</a>
    <a href="adddiscounts.php">📊 Promotion</a>
    <a href="viewreview.php">⭐️ View Reviews</a>
    <a href="admin_subscriptions.php" class="active">📝 View Subscriptions</a>
    <div class="logout">
        <a href="../login.php">🚪 Logout</a>
    </div>
</div>

<div class="main">
    <h1>Subscription Management</h1>
    
    <div class="card shadow-sm p-3">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">User</th>
                        <th scope="col">Lunchbox</th>
                        <th scope="col">End Date</th>
                        <th scope="col">Status</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($subscriptions) > 0): ?>
                        <?php foreach ($subscriptions as $sub): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($sub['id']); ?></td>
                                <td><?php echo htmlspecialchars($sub['username']); ?></td>
                                <td><?php echo htmlspecialchars($sub['lunchbox_name']); ?></td>
                                <td><?php echo htmlspecialchars($sub['end_date']); ?></td>
                                <td>
                                    <?php if ($sub['status'] == 'active'): ?>
                                        <span class="status-active"><?php echo htmlspecialchars($sub['status']); ?></span>
                                    <?php else: ?>
                                        <span class="status-deactive"><?php echo htmlspecialchars($sub['status']); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($sub['status'] == 'active'): ?>
                                        <form method="POST" action="admin_subscriptions.php" onsubmit="return confirm('Are you sure you want to deactivate this subscription?');">
                                            <input type="hidden" name="deactivate_id" value="<?php echo $sub['id']; ?>">
                                            <button type="submit" class="btn btn-deactivate btn-sm">Deactivate</button>
                                        </form>
                                    <?php else: ?>
                                        <button class="btn btn-secondary btn-sm" disabled>Deactivated</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted">No subscriptions found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>