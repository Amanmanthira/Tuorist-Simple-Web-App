<?php
require 'db.php';

if (!isset($_GET['token'])) {
    die("Invalid request.");
}

$token = $_GET['token'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE reset_token = ?");
$stmt->execute([$token]);
$user = $stmt->fetch();

if (!$user) {
    die("Invalid or expired token.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Reset Password</title>
</head>
<body style="font-family:sans-serif; background:#f2f2f2; padding:2rem; text-align:center;">

  <h2 style="color:#0066cc;">Reset Your Password</h2>
  <form action="update_password.php" method="POST" style="max-width:400px; margin:auto; background:#fff; padding:2rem; border-radius:10px;">
    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>" />
    <label>New Password</label><br>
    <input type="password" name="new_password" required style="width:100%; padding:0.6rem; margin:1rem 0;"><br>
    <button type="submit" style="padding:0.6rem 2rem; background:#0066cc; color:#fff; border:none; border-radius:5px;">Reset Password</button>
  </form>

</body>
</html>
