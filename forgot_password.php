<?php
require 'vendor/autoload.php';  // Composer autoloader

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        $token = bin2hex(random_bytes(32));
        $stmt = $pdo->prepare("UPDATE users SET reset_token = ? WHERE email = ?");
        $stmt->execute([$token, $email]);

        $resetLink = "http://localhost/project/reset_password.php?token=" . $token;

        // Send email
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'amanmanthira32326@gmail.com'; // your Gmail
            $mail->Password = 'omzd nplk oxtk aetf';   // Gmail app password
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('yourgmail@gmail.com', 'Explore World');
            $mail->addAddress($email, $user['fullname']);
            $mail->Subject = 'Password Reset - Explore World';
            $mail->Body = "Hi " . $user['fullname'] . ",\n\nClick to reset password:\n$resetLink";

            $mail->send();
            echo "Reset link sent to your email.";
        } catch (Exception $e) {
            echo "Failed to send email: {$mail->ErrorInfo}";
        }
    } else {
        echo "Email not found.";
    }
}
?>
