<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username_email = trim($_POST['username_email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username=? OR email=?");
    $stmt->execute([$username_email, $username_email]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        header("Location: admin_panel.php");
        exit;
    } else {
        echo "Invalid credentials. <a href='admin_login.html'>Try again</a>.";
    }
} else {
    header("Location: admin_login.html");
}
?>
