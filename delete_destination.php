<?php
session_start();
require 'admin_operations.php';

if (!isset($_GET['id'])) {
    header('Location: admin_panel.php');
    exit;
}

$id = $_GET['id'];

// Delete destination by id
$stmt = $pdo->prepare("DELETE FROM destinations WHERE id = ?");
$stmt->execute([$id]);

header('Location: admin_panel.php');
exit;
