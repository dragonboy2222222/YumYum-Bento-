<?php
session_start();
if (!isset($_SESSION["username"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../login.php");
    exit;
}

require_once "../dbconnect.php";

if (!isset($_GET['id'])) {
    header("Location: viewmenu.php");
    exit;
}

$id = (int)$_GET['id'];

// Delete menu
$stmt = $conn->prepare("DELETE FROM menus WHERE id = :id");
$stmt->execute([':id' => $id]);

$_SESSION['message'] = "Menu deleted successfully!";
header("Location: viewmenu.php");
exit;
?>
