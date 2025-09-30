<?php
session_start();
if (!isset($_SESSION["username"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../login.php");
    exit;
}

require_once "../dbconnect.php";

$id = $_GET['id'] ?? 0;

// Handle update
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $description = $_POST['description'];

    $updateImg = "";
    if (!empty($_FILES["image"]["name"])) {
        $targetDir = "../lunchbox_images/";
        $fileExt = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
        $imgPath = $targetDir . uniqid("lunchbox_", true) . "." . $fileExt;
        move_uploaded_file($_FILES["image"]["tmp_name"], $imgPath);

        // delete old image
        $stmt = $conn->prepare("SELECT image FROM lunchboxes WHERE id = ?");
        $stmt->execute([$id]);
        $old = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($old && file_exists($old['image'])) {
            unlink($old['image']);
        }

        $updateImg = ", image = :image";
    }

    try {
        $sql = "UPDATE lunchboxes 
                SET name = :name, price = :price, description = :description $updateImg 
                WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":name", $name);
        $stmt->bindParam(":price", $price);
        $stmt->bindParam(":description", $description);
        if ($updateImg) {
            $stmt->bindParam(":image", $imgPath);
        }
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        $_SESSION['message'] = "✅ Lunchbox updated successfully!";
        header("Location: viewProduct.php");
        exit;
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}

// Fetch lunchbox info
$stmt = $conn->prepare("SELECT * FROM lunchboxes WHERE id = ?");
$stmt->execute([$id]);
$lunchbox = $stmt->fetch(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Lunchbox</title>
    <style>
        body { font-family: Poppins, sans-serif; background:#f5f5f5; padding:40px; }
        form { max-width:600px; margin:auto; background:#fff; padding:20px; border-radius:10px; box-shadow:0 5px 15px rgba(0,0,0,0.1);}
        input, textarea { width:100%; padding:10px; margin:10px 0; border:1px solid #ddd; border-radius:6px; }
        button { padding:10px 20px; background:#6a3e6f; color:#fff; border:none; border-radius:6px; font-weight:600; cursor:pointer; }
        button:hover { background:#4a284e; }
        img { margin:10px 0; border-radius:6px; }
    </style>
</head>
<body>
    <h2>Edit Lunchbox</h2>
    <form method="post" enctype="multipart/form-data">
        <input type="text" name="name" value="<?= htmlspecialchars($lunchbox['name']) ?>" required>
        <input type="number" step="0.01" name="price" value="<?= $lunchbox['price'] ?>" required>
        <textarea name="description" required><?= htmlspecialchars($lunchbox['description']) ?></textarea>

        <p>Current Image:</p>
        <?php if (!empty($lunchbox['image'])): ?>
            <img src="<?= $lunchbox['image'] ?>" width="120"><br>
        <?php endif; ?>
        <input type="file" name="image">

        <button type="submit">Update Lunchbox</button>
    </form>
</body>
</html>
