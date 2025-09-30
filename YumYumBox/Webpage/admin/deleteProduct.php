<?php
session_start();
if (!isset($_SESSION["username"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../login.php");
    exit;
}

require_once "../dbconnect.php";

if (isset($_GET['id'])) {
    $id = intval($_GET['id']); // sanitize ID

    try {
        // First get image path to delete the file
        $stmt = $conn->prepare("SELECT image FROM lunchboxes WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row && !empty($row['image']) && file_exists($row['image'])) {
            unlink($row['image']); // delete old image file
        }

        // Delete lunchbox from DB
        $sql = "DELETE FROM lunchboxes WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$id]);

        $_SESSION['message'] = "✅ Lunchbox deleted successfully!";
    } catch (PDOException $e) {
        $_SESSION['message'] = "❌ Error deleting lunchbox: " . $e->getMessage();
    }
}

header("Location: viewProduct.php");
exit;
?>
