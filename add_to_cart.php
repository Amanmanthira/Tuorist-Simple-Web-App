<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: login.html");
  exit();
}

$user_id = $_SESSION['user_id'];
$product_id = $_POST['product_id'];
$reference_number = $_POST['reference_number'];

// Check if already added
$check = $pdo->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
$check->execute([$user_id, $product_id]);

if ($check->rowCount() > 0) {
  // If already exists, just increase quantity
  $pdo->prepare("UPDATE cart SET quantity = quantity + 1 WHERE user_id = ? AND product_id = ?")
      ->execute([$user_id, $product_id]);
} else {
  // Add new item
  $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity, reference_number) VALUES (?, ?, 1, ?)")
      ->execute([$user_id, $product_id, $reference_number]);
}

header("Location: shop.php"); // go back to product page
exit();
?>
