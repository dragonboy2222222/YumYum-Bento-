<?php
session_start();
if (!isset($_SESSION["username"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../login.php");
    exit;
}

require_once "../dbconnect.php";

if (isset($_GET["id"])) {
    $id = $_GET["id"];

    try {
        $sql = "DELETE FROM plans WHERE id=?";
        $stmt = $conn->prepare($sql);
        $flag = $stmt->execute([$id]);

        if ($flag) {
            $_SESSION['message'] = "🗑️ Plan with ID $id deleted successfully!";
        } else {
            $_SESSION['message'] = "❌ Failed to delete plan!";
        }
        header("Location: insertPlans.php");
        exit;
    } catch (PDOException $e) {
        echo $e->getMessage();
    }
}
?>
