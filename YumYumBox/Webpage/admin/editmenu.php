<?php
session_start();
if (!isset($_SESSION["username"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../login.php");
    exit;
}
require_once "../dbconnect.php";

// Fetch menu to edit
if (!isset($_GET['id'])) {
    header("Location: viewmenu.php");
    exit;
}

$id = (int)$_GET['id'];

// Get all lunchbox categories
$lunchboxes = $conn->query("SELECT id, name FROM lunchboxes ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

// If form submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $lunchbox_id = (int)$_POST['lunchbox_id'];

    // Handle image upload if provided
    $image = $_POST['current_image']; // keep old image by default
    if (!empty($_FILES['image']['name'])) {
        $targetDir = "../uploads/";
        $newFileName = time() . "_" . basename($_FILES['image']['name']);
        $targetFile = $targetDir . $newFileName;

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
            $image = $newFileName;
        }
    }

    // Update DB
    $sql = "UPDATE menus SET name = :name, description = :description, lunchbox_id = :lunchbox_id, image = :image WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':name' => $name,
        ':description' => $description,
        ':lunchbox_id' => $lunchbox_id,
        ':image' => $image,
        ':id' => $id
    ]);

    $_SESSION['message'] = "Menu updated successfully!";
    header("Location: viewmenu.php");
    exit;
}

// Fetch current menu details
$stmt = $conn->prepare("SELECT * FROM menus WHERE id = :id");
$stmt->execute([':id' => $id]);
$menu = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$menu) {
    $_SESSION['message'] = "Menu not found!";
    header("Location: viewmenu.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Menu</title>
<style>
    body { font-family: Arial, sans-serif; background: #f8f4ec; padding: 40px; }
    form { background: #fff; padding: 25px; border-radius: 8px; width: 400px; margin: auto; box-shadow: 0 3px 8px rgba(0,0,0,0.1); }
    label { display: block; margin-top: 15px; font-weight: bold; }
    input, select, textarea { width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #ccc; border-radius: 6px; }
    button { margin-top: 20px; padding: 10px 15px; background: #cc3300; color: #fff; border: none; border-radius: 6px; cursor: pointer; }
    button:hover { background: #993333; }
    img { margin-top: 10px; border-radius: 6px; max-width: 100px; }
</style>
</head>
<body>
<h2>Edit Menu</h2>
<form method="post" enctype="multipart/form-data">
    <label>Menu Name</label>
    <input type="text" name="name" value="<?= htmlspecialchars($menu['name']) ?>" required>

    <label>Description</label>
    <textarea name="description" rows="4"><?= htmlspecialchars($menu['description']) ?></textarea>

    <label>Lunchbox Category</label>
    <select name="lunchbox_id" required>
        <?php foreach ($lunchboxes as $l): ?>
            <option value="<?= $l['id'] ?>" <?= $menu['lunchbox_id'] == $l['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($l['name']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label>Image</label>
    <?php if (!empty($menu['image'])): ?>
        <br><img src="../uploads/<?= htmlspecialchars($menu['image']) ?>" alt="menu image">
    <?php endif; ?>
    <input type="file" name="image">
    <input type="hidden" name="current_image" value="<?= htmlspecialchars($menu['image']) ?>">

    <button type="submit">Update Menu</button>
</form>
</body>
</html>
