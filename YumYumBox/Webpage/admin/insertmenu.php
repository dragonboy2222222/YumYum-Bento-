<?php
session_start();
if (!isset($_SESSION["username"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../login.php");
    exit;
}

require_once "../dbconnect.php";

if (isset($_POST["insertBtn"])) {
    $name = $_POST["mname"];
    $description = $_POST["description"];
    $lunchbox_id = $_POST["lunchbox_id"];
    $fileImage = $_FILES["menuImage"];
    $filePath = "../lunchbox_images/" . basename($fileImage['name']);

    if (move_uploaded_file($fileImage['tmp_name'], $filePath)) {
        try {
            $sql = "INSERT INTO menus (name, description, image, lunchbox_id) 
                    VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $flag = $stmt->execute([$name, $description, $filePath, $lunchbox_id]);
            $id = $conn->lastInsertId();
            if ($flag) {
                $_SESSION['message'] = "✅ Menu with ID $id inserted successfully!";
                header("Location: viewMenu.php");
                exit;
            }
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    } else {
        echo "❌ File upload failed!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insert Menu</title>
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

.form-card {
    background: var(--white);
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    max-width: 700px;
    width: 100%;
    margin: auto;
}

.form-card label {
    font-weight: 600;
    margin-bottom: 8px;
    display: block;
}

.form-card input,
.form-card textarea,
.form-card select {
    width: 100%;
    padding: 10px;
    margin-bottom: 18px;
    border-radius: 8px;
    border: 1px solid var(--gray-light);
}

.form-card button {
    background-color: var(--red-medium);
    border: none;
    padding: 12px 20px;
    border-radius: 8px;
    font-weight: 600;
    color: var(--white);
    cursor: pointer;
    transition: all 0.3s ease;
}

.form-card button:hover {
    background-color: var(--red-dark);
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
}
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>YumYum Admin</h2>
    <a href="dashboard.php" >📊 Dashboard</a>
    <a href="insertProduct.php" >🍱 Manage Lunchboxes</a>
    <a href="viewUser.php">📋 View Users</a>
    <a href="viewProduct.php" >📦 Lunchbox Reports</a>
    <a href="insertmenu.php" class="active">🧾 Insert Menus</a>
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
        <h1>Insert Menu</h1>
        <div class="form-card">
            <form action="insertMenu.php" method="post" enctype="multipart/form-data">
                <label>Menu Name</label>
                <input type="text" name="mname" required>

                <label>Description</label>
                <textarea name="description" rows="4"></textarea>

               

                <label>Lunchbox Category</label>
                <select name="lunchbox_id" required>
                    <?php
                    $categories = $conn->query("SELECT id, name FROM lunchboxes");
                    while ($row = $categories->fetch(PDO::FETCH_ASSOC)) {
                        echo "<option value='{$row['id']}'>{$row['name']}</option>";
                    }
                    ?>
                </select>

                <label>Menu Image</label>
                <input type="file" name="menuImage" required>

                <button type="submit" name="insertBtn">➕ Insert Menu</button>
            </form>
        </div>
    </div>

</body>
</html>
