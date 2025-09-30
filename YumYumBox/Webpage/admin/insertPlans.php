<?php
session_start();
if (!isset($_SESSION["username"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../login.php");
    exit;
}

require_once "../dbconnect.php";

// Insert logic
if (isset($_POST["insertBtn"])) {
    $name = $_POST["pname"];
    $duration_days = $_POST["duration_days"];

    try {
        $sql = "INSERT INTO plans (name, duration_days) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $flag = $stmt->execute([$name, $duration_days]);
        $id = $conn->lastInsertId();
        if ($flag) {
            $_SESSION['message'] = "✅ Plan with ID $id inserted successfully!";
            header("Location: insertPlans.php");
            exit;
        }
    } catch (PDOException $e) {
        echo $e->getMessage();
    }
}

// Fetch all plans
$plans = $conn->query("SELECT * FROM plans ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insert Plans</title>
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
            margin-bottom: 40px;
            max-width: 600px;
        }

        .form-card form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .form-card label {
            font-weight: 600;
            color: var(--gray-dark);
        }

        .form-card input {
            padding: 12px;
            border: 1px solid var(--gray-light);
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-card input:focus {
            outline: none;
            border-color: var(--red-medium);
        }

        .form-card button {
            padding: 15px;
            background-color: var(--red-medium);
            color: var(--white);
            border: none;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .form-card button:hover {
            background-color: var(--red-dark);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: var(--white);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            margin-top: 20px;
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

        .btn {
            display: inline-block;
            padding: 8px 12px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9em;
            transition: background-color 0.3s ease;
        }

        .btn-edit {
            background-color: var(--red-medium);
            color: var(--white);
        }

        .btn-edit:hover {
            background-color: #e65c00;
        }
        
        .btn-delete {
            background-color: #ff4d4d;
            color: var(--white);
        }

        .btn-delete:hover {
            background-color: #ff1a1a;
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
    <a href="insertmenu.php">🧾 Insert Menus</a>
    <a href="viewmenu.php">📖 View Menus</a>
    <a href="insertPlans.php" class="active">📅 Insert Plans</a>
    <a href="adddiscounts.php">📊 Promotion</a>
    <a href="viewreview.php" >⭐️ View Reviews</a>
    <a href="admin_subscriptions.php" >📝 View Subscriptions</a>

             <div class="logout">
        <a href="../login.php">🚪 Logout</a>
    </div>
        </div>
    </div>

    <div class="main">
        <h1>Insert Plan</h1>
        <div class="form-card">
            <form action="insertPlans.php" method="post">
                <label>Plan Name</label>
                <input type="text" name="pname" required>

                <label>Duration (Days)</label>
                <input type="number" name="duration_days" required>

                <button type="submit" name="insertBtn">➕ Insert Plan</button>
            </form>
        </div>

        <h1>All Plans</h1>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Plan Name</th>
                    <th>Duration (Days)</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($plans as $plan): ?>
                    <tr>
                        <td><?php echo $plan['id']; ?></td>
                        <td><?php echo htmlspecialchars($plan['name']); ?></td>
                        <td><?php echo $plan['duration_days']; ?></td>
                        <td>
                            <a href="editPlans.php?id=<?php echo $plan['id']; ?>" class="btn btn-edit">✏️ Edit</a>
                            <a href="deletePlans.php?id=<?php echo $plan['id']; ?>" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this plan?');">🗑️ Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</body>
</html>