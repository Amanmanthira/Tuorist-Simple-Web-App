<?php
session_start();
require 'admin_operations.php'; // to have $pdo and session check

if (!isset($_GET['id'])) {
    header('Location: admin_panel.php');
    exit;
}

$id = $_GET['id'];

// Fetch destination to edit
$stmt = $pdo->prepare("SELECT * FROM destinations WHERE id = ?");
$stmt->execute([$id]);
$destination = $stmt->fetch();

if (!$destination) {
    echo "Destination not found.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $province = $_POST['province'];
    $location = $_POST['location'];
    $type = $_POST['type'];
    $description = $_POST['description'];

    $imagePath = $destination['image']; // keep old image if no new upload

    if (!empty($_FILES['image']['name'])) {
        $targetDir = "uploads/";
        $imagePath = $targetDir . basename($_FILES["image"]["name"]);
        move_uploaded_file($_FILES["image"]["tmp_name"], $imagePath);
    }

    $stmt = $pdo->prepare("UPDATE destinations SET name=?, province=?, location=?, type=?, image=?, description=? WHERE id=?");
    $stmt->execute([$name, $province, $location, $type, $imagePath, $description, $id]);

    header('Location: admin_panel.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Edit Destination</title>
</head>
<body>
  <h2>Edit Destination</h2>
  <form method="POST" enctype="multipart/form-data">
    <input type="text" name="name" placeholder="Destination Name" value="<?= htmlspecialchars($destination['name']) ?>" required />
    <input type="text" name="province" placeholder="Province" value="<?= htmlspecialchars($destination['province']) ?>" required />
    <input type="text" name="location" placeholder="Location" value="<?= htmlspecialchars($destination['location']) ?>" required />
    <input type="text" name="type" placeholder="Type" value="<?= htmlspecialchars($destination['type']) ?>" required />
    <p>Current image:</p>
    <?php if ($destination['image']): ?>
      <img src="<?= htmlspecialchars($destination['image']) ?>" width="120" style="border-radius:4px;" />
    <?php else: ?>
      <p>No image uploaded</p>
    <?php endif; ?>
    <p>Upload new image (optional):</p>
    <input type="file" name="image" accept="image/*" />
    <input type="text" name="description" placeholder="Description" value="<?= htmlspecialchars($destination['description']) ?>" required />
    <button type="submit">Save Changes</button>
  </form>
  <p><a href="admin_panel.php">Back to Admin Panel</a></p>
</body>
</html>
