<?php
session_start();
if (!isset($_SESSION["username"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../login.php");
    exit;
}

require_once "../dbconnect.php";

if (isset($_GET["id"])) {
    $id = $_GET["id"];
    $stmt = $conn->prepare("SELECT * FROM plans WHERE id=?");
    $stmt->execute([$id]);
    $plan = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (isset($_POST["updateBtn"])) {
    $id = $_POST["id"];
    $name = $_POST["pname"];
    $duration_days = $_POST["duration_days"];

    try {
        $sql = "UPDATE plans SET name=?, duration_days=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $flag = $stmt->execute([$name, $duration_days, $id]);
        if ($flag) {
            $_SESSION['message'] = "✅ Plan with ID $id updated successfully!";
            header("Location: insertPlans.php");
            exit;
        }
    } catch (PDOException $e) {
        echo $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Plan</title>
</head>
<body>
    <h1>Edit Plan</h1>
    <form action="editPlans.php" method="post">
        <input type="hidden" name="id" value="<?php echo $plan['id']; ?>">

        <label>Plan Name:</label>
        <input type="text" name="pname" value="<?php echo $plan['name']; ?>" required><br><br>

        <label>Duration (Days):</label>
        <input type="number" name="duration_days" value="<?php echo $plan['duration_days']; ?>" required><br><br>

        <button type="submit" name="updateBtn">💾 Update Plan</button>
    </form>
</body>
</html>
