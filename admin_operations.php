<?php
// admin_operations.php

require 'db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.html");
    exit;
}



// ADD DESTINATION LOGIC
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_destination'])) {
    $name = $_POST['name'];
    $province = $_POST['province'];
    $location = $_POST['location'];
    $type = $_POST['type'];
    $description = $_POST['description'];

    $imagePath = '';
    if (!empty($_FILES['image']['name'])) {
        $targetDir = "uploads/";
        $imagePath = $targetDir . basename($_FILES["image"]["name"]);
        move_uploaded_file($_FILES["image"]["tmp_name"], $imagePath);
    }

    $stmt = $pdo->prepare("INSERT INTO destinations (name, province, location, type, image, description) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$name, $province, $location, $type, $imagePath, $description]);
}

// EDIT DESTINATION LOGIC
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_destination'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $province = $_POST['province'];
    $location = $_POST['location'];
    $type = $_POST['type'];
    $description = $_POST['description'];

    // Fetch existing image path to keep if no new image uploaded
    $stmt = $pdo->prepare("SELECT image FROM destinations WHERE id = ?");
    $stmt->execute([$id]);
    $existing = $stmt->fetch();
    $imagePath = $existing['image'] ?? '';

    // Handle new image upload if any
    if (!empty($_FILES['image']['name'])) {
        $targetDir = "uploads/";
        $imagePath = $targetDir . basename($_FILES["image"]["name"]);
        move_uploaded_file($_FILES["image"]["tmp_name"], $imagePath);
    }

    $stmt = $pdo->prepare("UPDATE destinations SET name=?, province=?, location=?, type=?, image=?, description=? WHERE id=?");
    $stmt->execute([$name, $province, $location, $type, $imagePath, $description, $id]);
}

// DELETE DESTINATION LOGIC
if (isset($_GET['delete_destination'])) {
    $id = $_GET['delete_destination'];

    // Optional: Delete image file from server if exists
    $stmt = $pdo->prepare("SELECT image FROM destinations WHERE id = ?");
    $stmt->execute([$id]);
    $destination = $stmt->fetch();
    if ($destination && !empty($destination['image']) && file_exists($destination['image'])) {
        unlink($destination['image']);
    }

    $stmt = $pdo->prepare("DELETE FROM destinations WHERE id = ?");
    $stmt->execute([$id]);

    // Redirect to avoid resubmission
    header("Location: admin_panel.php");
    exit;
}


$destinations = $pdo->query("SELECT * FROM destinations ORDER BY created_at DESC")->fetchAll();

// HOTEL INSERTION LOGIC
if (isset($_POST['add_hotel'])) {
    $name = $_POST['name'];
    $province = $_POST['province'];
    $location = $_POST['location'];
    $stars = $_POST['stars'];
    $price_min = $_POST['price_min'];
    $price_max = $_POST['price_max'];
    $description = $_POST['description'];

    $image = '';
    if (!empty($_FILES['image']['name'])) {
        $imagePath = 'uploads/hotels/' . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $imagePath);
        $image = $imagePath;
    }

    $stmt = $pdo->prepare("INSERT INTO hotels (name, province, location, stars, price_min, price_max, image, description)
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$name, $province, $location, $stars, $price_min, $price_max, $image, $description]);
}


// FETCH HOTELS DATA
$hotels = $pdo->query("SELECT * FROM hotels ORDER BY created_at DESC")->fetchAll();

?>
