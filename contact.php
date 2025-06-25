<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $mail = new PHPMailer(true);

  try {
      // Server settings
      $mail->isSMTP();
      $mail->Host = 'smtp.gmail.com';
      $mail->SMTPAuth = true;
      $mail->Username = 'yourgmail@gmail.com'; // Replace with your Gmail
      $mail->Password = 'your-app-password'; // Use an App Password
      $mail->SMTPSecure = 'tls';
      $mail->Port = 587;

      // Recipients
      $mail->setFrom($_POST['email'], $_POST['name']);
      $mail->addAddress('yourgmail@gmail.com'); // Your receiving email

      // Content
      $mail->isHTML(true);
      $mail->Subject = 'New Message from Contact Form';
      $mail->Body = nl2br("Name: " . $_POST['name'] . "\nEmail: " . $_POST['email'] . "\nMessage:\n" . $_POST['message']);

      $mail->send();
      $success = "Message has been sent!";
  } catch (Exception $e) {
      $error = "Mailer Error: " . $mail->ErrorInfo;
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <!-- (your head code is unchanged, include styles, fonts, etc.) -->
  <meta charset="UTF-8" />
  <title>Contact Us</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- all your CSS links -->
</head>
<body>
  <!-- Your nav bar -->

  <section class="contact-section">
    <h1 style="font-size:2.5rem;color:#0077b6;">Get In Touch</h1>
    <p style="color:#555;max-width:700px;margin:auto;">We'd love to hear from you! Whether it's feedback, support, or partnership opportunities — drop us a message.</p>

    <div class="contact-container">
      <div class="contact-form">
        <h2>Send a Message</h2>
        <?php if ($success): ?>
          <p style="color:green;"><?php echo $success; ?></p>
        <?php elseif ($error): ?>
          <p style="color:red;"><?php echo $error; ?></p>
        <?php endif; ?>

        <form method="POST" action="">
          <input type="text" name="name" placeholder="Your Name" required />
          <input type="email" name="email" placeholder="Your Email" required />
          <textarea name="message" rows="5" placeholder="Your Message" required></textarea>
          <button type="submit">Submit</button>
        </form>
      </div>

      <!-- Your contact info section -->
    </div>
  </section>

  <!-- Your footer and scripts -->
</body>
</html>
