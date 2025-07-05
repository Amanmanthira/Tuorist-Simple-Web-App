<?php
session_start();
require 'db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.html");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: admin_panel.php");
    exit;
}

$id = $_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM hotels WHERE id = ?");
$stmt->execute([$id]);
$hotel = $stmt->fetch();

if (!$hotel) {
    echo "Hotel not found.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $province = $_POST['province'];
    $location = $_POST['location'];
    $stars = $_POST['stars'];
    $price_min = $_POST['price_min'];
    $price_max = $_POST['price_max'];
    $description = $_POST['description'];
    $image = $hotel['image'];

    if (!empty($_FILES['image']['name'])) {
        $imagePath = 'uploads/hotels/' . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $imagePath);
        $image = $imagePath;
    }

    $stmt = $pdo->prepare("UPDATE hotels SET name = ?, province = ?, location = ?, stars = ?, price_min = ?, price_max = ?, image = ?, description = ? WHERE id = ?");
    $stmt->execute([$name, $province, $location, $stars, $price_min, $price_max, $image, $description, $hotel['id']]);

    header("Location: admin_panel.php"); 
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Edit Hotel</title>
</head>
<body>
<h2>Edit Hotel</h2>
<form method="POST" enctype="multipart/form-data">
  <input type="text" name="name" value="<?= htmlspecialchars($hotel['name']) ?>" required />
  <input type="text" name="province" value="<?= htmlspecialchars($hotel['province']) ?>" required />
  <input type="text" name="location" value="<?= htmlspecialchars($hotel['location']) ?>" required />
  <input type="number" name="stars" min="1" max="5" value="<?= htmlspecialchars($hotel['stars']) ?>" required />
  
<input type="number" name="price_min" value="<?= htmlspecialchars($hotel['price_min']) ?>" placeholder="Min Price" required />
<input type="number" name="price_max" value="<?= htmlspecialchars($hotel['price_max']) ?>" placeholder="Max Price" required />
  <p>Current Image:</p>
  <?php if ($hotel['image']): ?>
    <img src="<?= htmlspecialchars($hotel['image']) ?>" width="120" style="border-radius:4px;" />
  <?php else: ?>
    <p>No image uploaded.</p>
  <?php endif; ?>

  <p>Upload new image (optional):</p>
  <input type="file" name="image" accept="image/*" />

  <input type="text" name="description" value="<?= htmlspecialchars($hotel['description']) ?>" required />
  
  <button type="submit">Save Changes</button>
</form>
<p><a href="admin_panel.php">Back to Admin Panel</a></p>
</body>
</html>
