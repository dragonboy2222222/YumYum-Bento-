<?php
session_start();

// ✅ Only allow admins
if (!isset($_SESSION["username"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../login.php");
    exit;
}

require_once "../dbconnect.php";

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $reviewId = (int)$_GET['id'];

    try {
        // Delete the review
        $sql = "DELETE FROM reviews WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $reviewId, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $_SESSION['message'] = "✅ Review deleted successfully.";
        } else {
            $_SESSION['message'] = "⚠️ Review not found or already deleted.";
        }
    } catch (PDOException $e) {
        $_SESSION['message'] = "❌ Error deleting review: " . $e->getMessage();
    }
} else {
    $_SESSION['message'] = "⚠️ Invalid review ID.";
}

// Redirect back to viewreview.php
header("Location: viewreview.php");
exit;
