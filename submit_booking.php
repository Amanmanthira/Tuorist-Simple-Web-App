<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

// DB Connection
$conn = new mysqli("localhost", "root", "", "project");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create table if needed (use your actual columns)
$conn->query("CREATE TABLE IF NOT EXISTS orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  guide_id INT NOT NULL,
  booking_date DATE NOT NULL,
  tour_type VARCHAR(50),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");
$user_id = $_SESSION['user_id'];
$guide_id = $_POST['guide_id'];
$booking_date = $_POST['booking_date'];

// Prepare insert statement without tour_type
$stmt = $conn->prepare("INSERT INTO orders (user_id, guide_id, booking_date) VALUES (?, ?, ?)");
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("iis", $user_id, $guide_id, $booking_date);

if ($stmt->execute()) {
    echo "✅ Booking confirmed! <a href='booking.php'>Book another</a>";
} else {
    echo "❌ Booking failed: " . $stmt->error;
}

?>
