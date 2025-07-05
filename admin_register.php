<?php
session_start();
require 'db.php';

// === AUTO ADD ADMIN IF NOT EXISTS ===
$autoAdminUsername = 'admin';
$autoAdminEmail = 'admin@gmail.com';
$autoAdminPassword = 'admin123';

// Check if auto admin exists
$stmt = $pdo->prepare("SELECT id FROM admins WHERE username=? OR email=?");
$stmt->execute([$autoAdminUsername, $autoAdminEmail]);
if (!$stmt->fetch()) {
    $hashed = password_hash($autoAdminPassword, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO admins (username, email, password) VALUES (?, ?, ?)");
    $stmt->execute([$autoAdminUsername, $autoAdminEmail, $hashed]);
    echo "✅ Default admin added! <br>Username: {$autoAdminUsername}<br>Password: {$autoAdminPassword}<br><a href='admin_login.html'>Go to Login</a><br><br>";
}

// === NORMAL REGISTER FLOW (with POST form) ===
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

    // Check if username or email exists
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
    // Redirect to form only if not auto-creating
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        header("Location: admin_register.html");
    }
}
?>
