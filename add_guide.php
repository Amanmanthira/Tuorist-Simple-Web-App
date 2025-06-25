<?php
session_start();
require 'db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.html");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $language = trim($_POST['language']);
    $description = trim($_POST['description']);

    if (!$name) {
        die("Guide name is required.");
    }

    $stmt = $pdo->prepare("INSERT INTO guides (name, language, description) VALUES (?, ?, ?)");
    $stmt->execute([$name, $language, $description]);

    header("Location: admin_panel.php");
    exit;
} else {
    header("Location: admin_panel.php");
    exit;
}
?>
