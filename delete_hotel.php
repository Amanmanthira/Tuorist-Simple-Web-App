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

$stmt = $pdo->prepare("DELETE FROM hotels WHERE id = ?");
$stmt->execute([$id]);

header("Location: admin_panel.php");
exit;
