<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

// DB connection
$conn = new mysqli("localhost", "root", "", "project");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch guides and hotels
$guides = $conn->query("SELECT id, name FROM guides");
$hotels = $conn->query("SELECT id, name FROM hotels");

?>
<!DOCTYPE html>
<html>
<head>
  <title>Book a Tour</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;600&display=swap" rel="stylesheet" />
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: #f0f8ff;
      padding: 2rem;
      margin: 0;
    }
    h2 {
      text-align: center;
      color: #0066cc;
    }
    .booking-form {
      max-width: 600px;
      margin: 2rem auto;
      background: #fff;
      padding: 2rem;
      border-radius: 12px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    }
    .booking-form label {
      font-weight: bold;
      margin-top: 1rem;
      display: block;
      color: #333;
    }
    .booking-form input,
    .booking-form select {
      width: 100%;
      padding: 0.6rem;
      margin-top: 0.3rem;
      border-radius: 6px;
      border: 1px solid #ccc;
      font-size: 1rem;
    }
    .booking-form button {
      margin-top: 2rem;
      width: 100%;
      background: #0066cc;
      color: white;
      padding: 0.8rem;
      font-size: 1.1rem;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-weight: bold;
    }
    .booking-form button:hover {
      background: #004a99;
    }
    .logout-link {
      text-align: center;
      margin-top: 2rem;
    }
  </style>
</head>
<body>

<h2>Book Your Guide & Hotel</h2>

<div class="booking-form">
  <form action="submit_booking.php" method="POST">
    
    <label for="guide_id">Choose a Guide:</label>
    <select name="guide_id" required>
      <option value="">-- Select Guide --</option>
      <?php while ($g = $guides->fetch_assoc()): ?>
        <option value="<?= $g['id'] ?>"><?= htmlspecialchars($g['name']) ?></option>
      <?php endwhile; ?>
    </select>

    <label for="hotel_id">Choose a Hotel:</label>
    <select name="hotel_id" required>
      <option value="">-- Select Hotel --</option>
      <?php while ($h = $hotels->fetch_assoc()): ?>
        <option value="<?= $h['id'] ?>"><?= htmlspecialchars($h['name']) ?></option>
      <?php endwhile; ?>
    </select>

    <label for="room_type">Room Type:</label>
    <select name="room_type" required>
      <option value="normal">Normal Room (with all meals)</option>
      <option value="luxury">Luxury Room (with all meals)</option>
    </select>

    <label for="tour_type">Tour Type:</label>
    <select name="tour_type" required>
      <option value="walking">Walking Tour</option>
      <option value="museum">Museum Visit</option>
      <option value="cultural">Cultural Experience</option>
    </select>

    <label for="people_count">How Many People:</label>
    <input type="number" name="people_count" min="1" required>

    <label for="start_date">Start Date:</label>
    <input type="date" name="start_date" required>

    <label for="end_date">End Date:</label>
    <input type="date" name="end_date" required>

    <button type="submit">Confirm Booking</button>
  </form>
</div>

<p class="logout-link"><a href="my_bookings.php">View Your Bookings</a></p>

</body>
</html>
