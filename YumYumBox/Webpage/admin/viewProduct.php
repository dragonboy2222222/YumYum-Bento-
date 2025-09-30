<?php
session_start();
if (!isset($_SESSION["username"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../login.php");
    exit;
}

require_once "../dbconnect.php";

try {
    $sql = "SELECT id, name, price, description, image
            FROM lunchboxes
            ORDER BY id DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $lunchboxes = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
<title>View Lunchboxes | Admin Dashboard</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

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

    /* Main content area styles from dashboard.php */
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

    /* Product card container for a grid layout */
    .product-card-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 25px;
    }

    /* Individual product card styling */
    .product-card {
        background: var(--white);
        border-radius: 12px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        overflow: hidden;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        display: flex;
        flex-direction: column;
    }

    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    }

    .product-card-image {
        width: 100%;
        height: 200px;
        overflow: hidden;
        display: flex;
        justify-content: center;
        align-items: center;
        background-color: var(--gray-light);
    }

    .product-card-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .product-card-content {
        padding: 20px;
        display: flex;
        flex-direction: column;
        flex-grow: 1;
    }

    .product-card-content h3 {
        color: var(--red-medium);
        margin-bottom: 10px;
        font-size: 1.4rem;
    }

    .price-stock {
        display: flex;
        justify-content: space-between;
        margin-bottom: 15px;
        font-weight: 600;
        color: var(--gray-dark);
    }

    .product-card-content p {
        color: var(--gray-dark);
        font-size: 0.9rem;
        flex-grow: 1;
        margin-bottom: 15px;
    }

    .product-card-actions {
        display: flex;
        justify-content: space-between;
        gap: 10px;
    }

    .product-card-actions a {
        display: inline-block;
        padding: 8px 12px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        transition: background-color 0.3s ease;
        text-align: center;
        flex-grow: 1;
    }

    .product-card-actions .edit {
        background-color: var(--red-medium);
        color: var(--white);
    }

    .product-card-actions .edit:hover {
        background-color: var(--red-dark);
    }

    .product-card-actions .delete {
        background-color: #f44336;
        color: var(--white);
    }

    .product-card-actions .delete:hover {
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
    <a href="viewProduct.php" class="active">📦 Lunchbox Reports</a>
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
        <h1>All Lunchboxes 📦</h1>

        <?php if ($message): ?>
            <div class="message"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <div class="product-card-container">
            <?php if (!empty($lunchboxes)): ?>
                <?php foreach ($lunchboxes as $lb): ?>
                    <div class="product-card">
                        <div class="product-card-image">
                            <?php if (!empty($lb['image'])): ?>
                                <img src="../admin/<?= htmlspecialchars($lb['image']) ?>" alt="lunchbox">
                            <?php endif; ?>
                        </div>
                        <div class="product-card-content">
                            <h3><?= htmlspecialchars($lb['name']) ?></h3>
                            <div class="price-stock">
                                <span>$<?= number_format((float)$lb['price'], 2) ?></span>
                                
                            </div>
                            <p><?= htmlspecialchars($lb['description']) ?></p>
                            <div class="product-card-actions">
                                <a href="editProduct.php?id=<?= (int)$lb['id'] ?>" class="edit">✏️ Edit</a>
                                <a href="deleteProduct.php?id=<?= (int)$lb['id'] ?>" class="delete"
                                   onclick="return confirm('Are you sure you want to delete this lunchbox?')">🗑 Delete</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No lunchboxes found.</p>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>