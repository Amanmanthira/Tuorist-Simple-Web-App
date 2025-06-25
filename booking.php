<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
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

// Fetch guides
$guides_result = $conn->query("SELECT id, name, language, description FROM guides");

if (!$guides_result) {
    die("Query Error: " . $conn->error);
} else {
    echo "<p style='text-align:center;'>Guides found: " . $guides_result->num_rows . "</p>";
}

?>
<!DOCTYPE html>
<html>
<head>
  <title>Book a Guide</title>
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
    .cards-container {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 2rem;
      padding: 2rem;
    }
    .guide-card {
      background: white;
      border-radius: 12px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
      padding: 1.5rem;
      transition: transform 0.3s ease;
      position: relative;
    }
    .guide-card:hover {
      transform: translateY(-5px);
    }
    .guide-card h3 {
      margin: 0 0 0.5rem;
      color: #333;
    }
    .guide-card p {
      margin: 0.2rem 0;
      color: #555;
    }
    .guide-card form {
      margin-top: 1rem;
    }
    .guide-card input[type="date"],
    .guide-card select,
    .guide-card button {
      width: 100%;
      padding: 0.5rem;
      margin-top: 0.5rem;
      font-size: 0.9rem;
    }
    .guide-card button {
      background: #0066cc;
      color: white;
      border: none;
      border-radius: 5px;
      font-weight: bold;
      cursor: pointer;
    }
    .guide-card button:hover {
      background: #004a99;
    }
    .logout-link {
      text-align: center;
      margin-top: 2rem;
    }
  </style>
</head>
<body>

<h2>Available Guides</h2>

<div class="cards-container">
  <?php
  if ($guides_result && $guides_result->num_rows > 0):
      while ($row = $guides_result->fetch_assoc()):
  ?>
    <div class="guide-card">
      <h3><?= htmlspecialchars($row['name']) ?></h3>
      <p><strong>Language:</strong> <?= htmlspecialchars($row['language']) ?></p>
      <p><?= htmlspecialchars($row['description']) ?></p>

      <form action="submit_booking.php" method="POST">
<input type="hidden" name="guide_id" value="<?= $row['id'] ?>">
        <label>Date of Tour:</label>
        <input type="date" name="booking_date" required>

        <label>Tour Type:</label>
        <select name="tour_type" required>
          <option value="walking">Walking Tour</option>
          <option value="museum">Museum Visit</option>
          <option value="cultural">Cultural Experience</option>
        </select>

        <button type="submit">Book Now</button>
      </form>
    </div>
  <?php
      endwhile;
  else:
      echo "<p style='text-align:center;'>No guides available at the moment.</p>";
  endif;
  ?>
</div>

<p class="logout-link"><a href="http://localhost/project/my_bookings.php">See Your Bookings</a></p>

</body>
</html>
