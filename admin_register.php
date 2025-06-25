<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (!$username || !$email || !$password || !$confirm_password) {
        die('Fill all fields.');
    }
    if ($password !== $confirm_password) {
        die('Passwords do not match.');
    }

    // Check existing
    $stmt = $pdo->prepare("SELECT id FROM admins WHERE username=? OR email=?");
    $stmt->execute([$username, $email]);
    if ($stmt->fetch()) {
        die('Username or email already taken.');
    }

    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO admins (username, email, password) VALUES (?, ?, ?)");
    $stmt->execute([$username, $email, $hashed]);

    echo "Admin registered! <a href='admin_login.html'>Login here</a>.";
} else {
    header("Location: admin_register.html");
}
?>
