<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $country = trim($_POST['country']);

    // Basic validation
    if (!$fullname || !$email || !$username || !$password || !$confirm_password || !$country) {
        die('Please fill all fields.');
    }

    if ($password !== $confirm_password) {
        die('Passwords do not match.');
    }

    // Check if email or username already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
    $stmt->execute([$email, $username]);
    if ($stmt->fetch()) {
        die('Email or Username already taken.');
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert user
    $stmt = $pdo->prepare("INSERT INTO users (fullname, email, username, password, country) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$fullname, $email, $username, $hashed_password, $country]);

    echo "Registration successful! <a href='login.html'>Login here</a>.";
} else {
    header('Location: user_register.html');
    exit;
}
?>
