<?php
session_start();
if (!isset($_SESSION["username"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../login.php");
    exit;
}

require_once "../dbconnect.php";

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

try {
    if ($search !== '') {
        $sql = "SELECT m.id, m.name, m.description, m.image, l.name AS lunchbox_name
                FROM menus m
                JOIN lunchboxes l ON m.lunchbox_id = l.id
                WHERE m.id LIKE :search 
                   OR l.name LIKE :search
                ORDER BY m.id DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':search' => "%$search%"]);
    } else {
        $sql = "SELECT m.id, m.name, m.description, m.image, l.name AS lunchbox_name
                FROM menus m
                JOIN lunchboxes l ON m.lunchbox_id = l.id
                ORDER BY m.id DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
    }
    $menus = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
<title>View Menus | Admin Dashboard</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
<style>
    /* Define new color variables based on the previous design */
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

    .message {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 8px;
        font-weight: 600;
        text-align: center;
    }

    .search-bar {
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .search-bar input[type="text"] {
        padding: 10px 15px;
        border: 1px solid var(--gray-light);
        border-radius: 8px;
        font-family: 'Poppins', sans-serif;
        font-size: 1rem;
        width: 300px;
        transition: border-color 0.3s ease;
    }

    .search-bar input[type="text"]:focus {
        outline: none;
        border-color: var(--red-medium);
    }
    
    .search-bar button {
        padding: 10px 20px;
        background-color: var(--red-medium);
        color: var(--white);
        border: none;
        border-radius: 8px;
        font-family: 'Poppins', sans-serif;
        font-weight: 600;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .search-bar button:hover {
        background-color: var(--red-dark);
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
        padding: 15px;
        text-align: left;
        border-bottom: 1px solid var(--gray-light);
    }

    thead th {
        background-color: var(--red-dark);
        color: var(--white);
        font-weight: 600;
    }

    tbody tr:hover {
        background-color: var(--cream);
    }
    
    .actions {
        display: flex;
        gap: 10px;
    }

    .actions a {
        padding: 8px 12px;
        border-radius: 6px;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.9em;
        transition: background-color 0.3s ease;
    }

    .actions a.edit {
        background-color: var(--red-medium);
        color: var(--white);
    }

    .actions a.edit:hover {
        background-color: #e65c00;
    }
    
    .actions a.delete {
        background-color: #ff4d4d;
        color: var(--white);
    }

    .actions a.delete:hover {
        background-color: #ff1a1a;
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
    <a href="viewmenu.php" class="active">📖 View Menus</a>
    <a href="insertPlans.php">📅 Insert Plans</a>
    <a href="adddiscounts.php">📊 Promotion</a>
    <a href="viewreview.php" >⭐️ View Reviews</a>
    <a href="admin_subscriptions.php" >📝 View Subscriptions</a>

    <div class="logout">
        <a href="../login.php">🚪 Logout</a>
    </div>
</div>

<div class="main">
    <h1>All Menus 📋</h1>

    <?php if ($message): ?>
        <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <div class="search-bar">
        <form method="get" action="">
            <input type="text" name="search" placeholder="Search by ID or Lunchbox Category" value="<?= htmlspecialchars($search) ?>">
            <button type="submit">Search</button>
            <?php if ($search !== ''): ?>
                <a href="viewmenu.php" style="margin-left:10px; font-weight:600; color:var(--red-dark); text-decoration:none;">❌ Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Menu Name</th>
                <th>Lunchbox Category</th>
                <th>Description</th>
                <th>Image</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($menus)): ?>
                <?php foreach ($menus as $m): ?>
                    <tr>
                        <td><?= (int)$m['id'] ?></td>
                        <td><?= htmlspecialchars($m['name']) ?></td>
                        <td><?= htmlspecialchars($m['lunchbox_name']) ?></td>
                        <td><?= htmlspecialchars($m['description']) ?></td>
                        <td>
                            <?php if (!empty($m['image'])): ?>
                                <img src="../uploads/<?= htmlspecialchars($m['image']) ?>" alt="menu" style="width:60px; height:60px; object-fit:cover; border-radius:6px;">
                            <?php else: ?>
                                <span>No Image</span>
                            <?php endif; ?>
                        </td>
                        <td class="actions">
                            <a href="editMenu.php?id=<?= (int)$m['id'] ?>" class="edit">✏️ Edit</a>
                            <a href="deleteMenu.php?id=<?= (int)$m['id'] ?>" class="delete"
                               onclick="return confirm('Are you sure you want to delete this menu?')">🗑 Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="6" style="text-align:center;">No menus found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>