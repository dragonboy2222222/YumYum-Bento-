<?php
session_start();
if (!isset($_SESSION["username"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../login.php");
    exit;
}

require_once "../dbconnect.php";

try {
    // Fetch all reviews, joining with the users table to display the reviewer's username
    $sql = "SELECT r.id, r.rating, r.review_text, r.created_at, u.username
            FROM reviews r
            JOIN users u ON r.user_id = u.id
            ORDER BY r.created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

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
<title>View Reviews | Admin Dashboard</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    /* Color variables from dashboard.php */
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

    /* Sidebar styles from dashboard.php */
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

    /* Main content area styles */
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

    /* Message box styling */
    .message {
        padding: 15px;
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
        border-radius: 8px;
        margin-bottom: 20px;
        text-align: center;
        font-weight: 600;
    }

    .review-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        background-color: var(--white);
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    }
    
    .review-table th, .review-table td {
        padding: 15px;
        text-align: left;
        border-bottom: 1px solid var(--gray-light);
    }

    .review-table th {
        background-color: var(--red-dark);
        color: var(--white);
        font-weight: 600;
    }
    
    .review-table tr:last-child td {
        border-bottom: none;
    }

    .review-table tbody tr:hover {
        background-color: var(--cream);
    }

    .review-text {
        max-width: 400px;
        overflow-wrap: break-word;
    }

    .rating .fa-star {
        color: #ffc107;
    }
    
    .action-button {
        display: inline-block;
        padding: 8px 12px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        transition: background-color 0.3s ease;
        text-align: center;
    }
    
    .action-button.delete {
        background-color: #f44336;
        color: var(--white);
    }

    .action-button.delete:hover {
        background-color: #d32f2f;
    }
</style>
</head>
<body>

    <div class="sidebar">
        <h2>YumYum Admin</h2>
        <a href="dashboard.php">📊 Dashboard</a>
        <a href="insertProduct.php">🍱 Manage Lunchboxes</a>
        <a href="viewUser.php">👥 View Users</a>
        <a href="viewProduct.php">📦 Lunchbox Reports</a>
        <a href="insertmenu.php">🧾 Insert Menus</a>
        <a href="viewmenu.php">📖 View Menus</a>
        <a href="insertPlans.php">📅 Insert Plans</a>
        <a href="adddiscounts.php">📊 Promotion</a>
        <a href="viewreview.php" class="active">⭐️ View Reviews</a>
        <a href="admin_subscriptions.php" >📝 View Subscriptions</a>

        <div class="logout">
            <a href="../login.php">🚪 Logout</a>
        </div>
    </div>

    <div class="main">
        <h1>Customer Reviews ⭐️</h1>

        <?php if ($message): ?>
            <div class="message"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <?php if (!empty($reviews)): ?>
            <table class="review-table">
                <thead>
                    <tr>
                        <th>Reviewer</th>
                        <th>Rating</th>
                        <th>Review</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reviews as $review): ?>
                        <tr>
                            <td><?= htmlspecialchars($review['username']) ?></td>
                            <td>
                                <div class="rating">
                                    <?php for ($i = 0; $i < 5; $i++): ?>
                                        <?php if ($i < $review['rating']): ?>
                                            <i class="fas fa-star"></i>
                                        <?php else: ?>
                                            <i class="far fa-star"></i>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                </div>
                            </td>
                            <td class="review-text"><?= nl2br(htmlspecialchars($review['review_text'])) ?></td>
                            <td><?= date('F j, Y', strtotime($review['created_at'])) ?></td>
                            <td>
                                <a href="deleteReview.php?id=<?= (int)$review['id'] ?>" class="action-button delete"
                                   onclick="return confirm('Are you sure you want to delete this review?')">
                                    Delete
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No reviews have been submitted yet.</p>
        <?php endif; ?>
    </div>

</body>
</html>