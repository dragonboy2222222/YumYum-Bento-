<?php
// Start a new session or resume the existing one.
session_start();

// Include the database connection script and PHPMailer library.
require_once "../dbconnect.php";
require '../../PHPMailer-master/src/PHPMailer.php';
require '../../PHPMailer-master/src/SMTP.php';
require '../../PHPMailer-master/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Redirect to the login page if the user is not authenticated or is not an admin.
if (!isset($_SESSION["username"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../login.php");
    exit;
}

// Map the target type from the form to the correct database table name.
$tableMap = [
    "plan" => "plans",
    "lunchbox" => "lunchboxes"
];

/**
 * Sends a discount notification email to all customers.
 * @param PDO $conn The database connection object.
 * @param array $discountDetails An associative array containing the discount details.
 * @return bool True on success, false on failure.
 */
function sendDiscountNotification($conn, $discountDetails) {
    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        
        // SECURITY ALERT: Hardcoded credentials. For a production environment,
        // these should be stored in environment variables or a secure config file.
        $mail->Username = 'theinpainghtun@gmail.com'; 
        $mail->Password = 'absd pjmt nkil ghjm'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Fetch all customer emails from the 'users' table.
        $stmt = $conn->prepare("SELECT email FROM users WHERE role = 'customer'");
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $mail->setFrom('no-reply@yumyum.com', 'YumYum Admin');
        $mail->isHTML(true);
        $mail->Subject = '🥳 New Discount Alert!';
        
        // Determine the correct symbol for the discount value.
        $discountSymbol = ($discountDetails['discount_type'] === 'percent') ? '%' : 'Ks';
        $discountText = $discountDetails['discount_value'] . ' ' . $discountSymbol;
        
        // Construct the HTML body of the email.
        $emailBody = "
            <h2>Special Offer Just for You!</h2>
            <p>A new discount has been added to our menu. Check out the details below:</p>
            <ul>
                <li><strong>Item:</strong> " . htmlspecialchars($discountDetails['name']) . "</li>
                <li><strong>Discount:</strong> " . htmlspecialchars($discountText) . "</li>
            </ul>
            <p>Visit our website to take advantage of this special offer!</p>
            <p>Best regards,<br>The YumYum Team</p>
        ";
        
        $mail->Body = $emailBody;
        $mail->AltBody = 'A new discount is available! Check out our website for details.';
        
        // Add all customer emails as recipients.
        foreach ($users as $user) {
            $mail->addAddress($user['email']);
        }
        
        // Send the email.
        $mail->send();
        return true;
    } catch (Exception $e) {
        // Log the error for debugging and return false.
        error_log("Mailer Error: " . $mail->ErrorInfo);
        return false;
    }
}

// --- PHP Logic for Handling Form Submissions ---

// Handle the "Update Discount" form submission via POST request.
if (isset($_POST['updateBtn'])) {
    $type = $_POST['target_type'];
    $id = $_POST['target_id'];
    $discount_type = $_POST['discount_type'];
    $discount_value = $_POST['discount_value'];

    try {
        $table = $tableMap[$type];
        
        // Use a prepared statement to safely update the database record.
        $sql = "UPDATE {$table} SET discount_type=?, discount_value=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$discount_type, $discount_value, $id]);

        // Fetch the details of the updated item to use in the email notification.
        $itemSql = "SELECT name, discount_type, discount_value FROM {$table} WHERE id=?";
        $itemStmt = $conn->prepare($itemSql);
        $itemStmt->execute([$id]);
        $discountDetails = $itemStmt->fetch(PDO::FETCH_ASSOC);

        // Send an email notification if the item details were successfully retrieved.
        if ($discountDetails) {
            sendDiscountNotification($conn, $discountDetails);
        }

        // Set a success message and redirect to prevent form resubmission on refresh.
        $_SESSION['message'] = "✅ Discount updated successfully!";
        header("Location: adddiscounts.php");
        exit;
    } catch (PDOException $e) {
        // Set an error message and redirect if a database error occurs.
        $_SESSION['error'] = "Error: " . $e->getMessage();
        header("Location: adddiscounts.php");
        exit;
    }
}

// Handle the "Delete Discount" request via GET.
// A POST request would be more secure for this action.
if (isset($_GET['delete']) && isset($_GET['type'])) {
    $type = $_GET['type'];
    $id = (int)$_GET['delete'];

    try {
        $table = $tableMap[$type];
        
        // Reset the discount fields to their default values (NULL and 0).
        $sql = "UPDATE {$table} SET discount_type=NULL, discount_value=0 WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$id]);

        // Set a success message and redirect.
        $_SESSION['message'] = "🗑️ Discount removed!";
        header("Location: adddiscounts.php");
        exit;
    } catch (PDOException $e) {
        // Set an error message and redirect on error.
        $_SESSION['error'] = "Error: " . $e->getMessage();
        header("Location: adddiscounts.php");
        exit;
    }
}

// Fetch all plans and lunchboxes from the database to display them in the HTML tables.
$plans = $conn->query("SELECT * FROM plans")->fetchAll(PDO::FETCH_ASSOC);
$lunchboxes = $conn->query("SELECT * FROM lunchboxes")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Discounts - YumYum Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        /* General styling for the page */
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
        
        /* Sidebar layout and styling */
        .sidebar {
            width: 280px;
            background-color: var(--red-dark);
            color: var(--white);
            padding: 30px;
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100%;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }

        .sidebar h2 {
            text-align: center;
            margin-bottom: 40px;
            font-weight: 700;
            color: var(--cream);
            text-transform: uppercase;
            letter-spacing: 1px;
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

        /* Main content area layout and styling */
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

        .card-container {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .card {
            background: var(--white);
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }

        .card h3 {
            color: var(--red-medium);
            font-weight: 600;
            margin-bottom: 1.5rem;
        }

        /* Table styling for displaying data */
        .table-responsive {
            margin-top: 20px;
            overflow-x: auto;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            background: var(--white);
            border-radius: 10px;
            overflow: hidden;
        }

        .table thead tr {
            background-color: var(--red-dark);
            color: var(--white);
        }

        .table thead th {
            border-bottom: none;
            padding: 1rem;
            text-align: left;
        }

        .table tbody tr {
            transition: background-color 0.3s ease;
        }
        
        .table tbody tr:hover {
            background-color: var(--gray-light);
        }
        
        .table td {
            vertical-align: middle;
            padding: 1rem;
            border-bottom: 1px solid var(--gray-light);
        }

        /* Form and button styling */
        .form-control, .form-select {
            display: inline-block;
            width: 150px;
            margin-right: 5px;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        .btn {
            padding: 8px 12px;
            border: none;
            border-radius: 6px;
            color: var(--white);
            cursor: pointer;
            transition: opacity 0.3s ease;
        }
        
        .btn-primary {
            background-color: var(--red-medium);
        }
        
        .btn-danger {
            background-color: #dc3545;
        }
        
        .btn:hover {
            opacity: 0.9;
        }
        
        .d-flex {
            display: flex;
            align-items: center;
        }
        
        .me-1 {
            margin-right: 0.25rem;
        }
        
        .d-inline {
            display: inline-block;
        }
        
        .alert-container {
            margin-bottom: 20px;
        }

        .alert {
            padding: 15px;
            border-radius: 8px;
            font-weight: 600;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border-color: #c3e6cb;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>YumYum Admin</h2>
    <a href="dashboard.php">📊 Dashboard</a>
    <a href="insertProduct.php">🍱 Manage Lunchboxes</a>
    <a href="viewUser.php">📋 View Users</a>
    <a href="viewProduct.php">📦 Lunchbox Reports</a>
    <a href="insertmenu.php">🧾 Insert Menus</a>
    <a href="viewmenu.php">📖 View Menus</a>
    <a href="insertPlans.php">📅 Insert Plans</a>
    <a href="adddiscounts.php" class="active">📊 Promotion</a>
    <a href="viewreview.php" >⭐️ View Reviews</a>
    <a href="admin_subscriptions.php" >📝 View Subscriptions</a>

    <div class="logout">
        <a href="../login.php">🚪 Logout</a>
    </div>
</div>

<div class="main">
    <h1>Manage Discounts</h1>
    
    <div class="alert-container">
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success"><?= $_SESSION['message']; unset($_SESSION['message']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
    </div>

    <div class="card-container">
        <div class="card">
            <h3 class="card-title">Discounts on Plans</h3>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Plan Name</th>
                            <th>Discount Type</th>
                            <th>Discount Value</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($plans as $plan): ?>
                        <tr>
                            <td><?= htmlspecialchars($plan['id']); ?></td>
                            <td><?= htmlspecialchars($plan['name']); ?></td>
                            <td><?= htmlspecialchars($plan['discount_type'] ?? '-'); ?></td>
                            <td><?= htmlspecialchars($plan['discount_value'] ?? 0); ?></td>
                            <td>
                                <form method="post" class="d-inline">
                                    <input type="hidden" name="target_type" value="plan">
                                    <input type="hidden" name="target_id" value="<?= htmlspecialchars($plan['id']); ?>">
                                    <div class="d-flex align-items-center">
                                        <select name="discount_type" class="form-select" required>
                                            <option value="percent" <?= ($plan['discount_type'] == 'percent') ? 'selected' : ''; ?>>%</option>
                                            <option value="fixed" <?= ($plan['discount_type'] == 'fixed') ? 'selected' : ''; ?>>Amount</option>
                                        </select>
                                        <input type="number" step="0.01" name="discount_value" value="<?= htmlspecialchars($plan['discount_value']); ?>" class="form-control me-1" required>
                                        <button type="submit" name="updateBtn" class="btn btn-sm btn-primary me-1">Update</button>
                                        <a href="?delete=<?= htmlspecialchars($plan['id']); ?>&type=plan" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to remove this discount?')">Delete</a>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <h3 class="card-title">Discounts on Lunchboxes</h3>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Lunchbox Name</th>
                            <th>Discount Type</th>
                            <th>Discount Value</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($lunchboxes as $lunchbox): ?>
                        <tr>
                            <td><?= htmlspecialchars($lunchbox['id']); ?></td>
                            <td><?= htmlspecialchars($lunchbox['name']); ?></td>
                            <td><?= htmlspecialchars($lunchbox['discount_type'] ?? '-'); ?></td>
                            <td><?= htmlspecialchars($lunchbox['discount_value'] ?? 0); ?></td>
                            <td>
                                <form method="post" class="d-inline">
                                    <input type="hidden" name="target_type" value="lunchbox">
                                    <input type="hidden" name="target_id" value="<?= htmlspecialchars($lunchbox['id']); ?>">
                                    <div class="d-flex align-items-center">
                                        <select name="discount_type" class="form-select" required>
                                            <option value="percent" <?= ($lunchbox['discount_type'] == 'percent') ? 'selected' : ''; ?>>%</option>
                                            <option value="fixed" <?= ($lunchbox['discount_type'] == 'fixed') ? 'selected' : ''; ?>>Amount</option>
                                        </select>
                                        <input type="number" step="0.01" name="discount_value" value="<?= htmlspecialchars($lunchbox['discount_value']); ?>" class="form-control me-1" required>
                                        <button type="submit" name="updateBtn" class="btn btn-sm btn-primary me-1">Update</button>
                                        <a href="?delete=<?= htmlspecialchars($lunchbox['id']); ?>&type=lunchbox" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to remove this discount?')">Delete</a>
                                    </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>
