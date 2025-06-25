<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$conn = new mysqli("localhost", "root", "", "project");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];

$sql = "
    SELECT o.booking_date, o.tour_type, o.created_at, g.name AS guide_name, g.language
    FROM orders o
    JOIN guides g ON o.guide_id = g.id
    WHERE o.user_id = ?
    ORDER BY o.created_at DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>My Bookings</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
  * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    transition: all 0.3s ease;
  }

  body {
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(to right, #f8f9fb, #eef3f7);
    color: #333;
    padding-top: 80px;
  }

  nav {
    position: fixed;
    top: 0; left: 0; right: 0;
    background: white;
    padding: 1rem 2rem;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    display: flex;
    justify-content: space-between;
    align-items: center;
    z-index: 1000;
  }

  nav .logo {
    font-size: 1.6rem;
    color: #0066cc;
    font-weight: 700;
  }

  nav .nav-links a {
    margin: 0 10px;
    color: #444;
    font-weight: 500;
    position: relative;
  }

  nav a:hover,
  nav a.active {
    color: #0066cc;
    font-weight: 600;
  }

  h1 {
    text-align: center;
    margin-bottom: 2rem;
    font-weight: 600;
    color: #222;
  }

  .bookings-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
    padding: 0 2rem 4rem;
    max-width: 1200px;
    margin: auto;
  }

  .booking-card {
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(8px);
    border-radius: 16px;
    padding: 1.5rem;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
    transition: transform 0.2s ease, box-shadow 0.3s ease;
  }

  .booking-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.15);
  }

  .booking-card h2 {
    margin-bottom: 0.5rem;
    color: #0066cc;
  }

  .booking-card p {
    margin: 0.4rem 0;
    line-height: 1.4;
  }

  .empty-message {
    text-align: center;
    padding: 3rem;
    color: #666;
    font-size: 1.2rem;
  }

  .btn-logout {
    position: fixed;
    bottom: 20px;
    right: 20px;
    background: #0066cc;
    color: white;
    padding: 10px 16px;
    border-radius: 8px;
    font-weight: 500;
    text-decoration: none;
    box-shadow: 0 4px 14px rgba(0, 102, 204, 0.4);
  }

  .btn-logout:hover {
    background: #0052a3;
  }

  .toggle {
    cursor: pointer;
    font-size: 1.2rem;
    color: #333;
  }

  .dark-mode {
    background: #1a1a1a;
    color: #eee;
  }

  .dark-mode nav {
    background: #222;
  }

  .dark-mode .booking-card {
    background: rgba(40, 40, 40, 0.9);
    color: #eee;
  }

  .dark-mode .btn-logout {
    background: #444;
  }
  
.center-button {
  text-align: center;
  margin-top: 2rem;
}

.booknew {
  background: #0066cc;
  color: white;
  padding: 9px 9px;
  text-decoration: none;
  font-weight: 500;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 102, 204, 0.3);
  transition: background 0.3s ease, transform 0.2s ease;
}

.booknew:hover {
  background: #005bb5;
  transform: translateY(-2px);
}

</style>
</head>
<body>

<nav>
  <div class="logo">🌍 Explore World</div>
  <div class="nav-links">
    <a href="index.html">Home</a>
    <a href="my_bookings.php" class="active">My Bookings</a>
    <a href="about.html">Who We Are</a>
    <a href="blogs.html">Blogs</a>
    <a href="journey.html">Journey</a>
    <a href="services.html">Services</a>
    <a href="weather-news.html">Weather & News</a>
    <a href="contact.html">Contact</a>
  </div>
  <div class="toggle" onclick="toggleDarkMode()" title="Toggle dark mode">
    <i class="fas fa-moon"></i>
  </div>
</nav>

<h1>My Bookings</h1>
<div class="center-button">
  <a href="http://localhost/project/booking.php" class="booknew">Book New</a>
</div>

<?php if ($result && $result->num_rows > 0): ?>
  <div class="bookings-container">
    <?php while ($booking = $result->fetch_assoc()): ?>
      <div class="booking-card">
        <h2><?= htmlspecialchars($booking['guide_name']) ?></h2>
        <p><strong>Language:</strong> <?= htmlspecialchars($booking['language']) ?></p>
        <p><strong>Tour Type:</strong> <?= htmlspecialchars(ucfirst($booking['tour_type'])) ?></p>
        <p><strong>Date of Tour:</strong> <?= htmlspecialchars($booking['booking_date']) ?></p>
        <p><small>Booked on: <?= date("F j, Y, g:i A", strtotime($booking['created_at'])) ?></small></p>
      </div>
    <?php endwhile; ?>
  </div>
<?php else: ?>
  <p class="empty-message">You have no bookings yet. <a href="booking.php">Book a guide now</a>.</p>
<?php endif; ?>

<a href="logout.php" class="btn-logout">Logout</a>

<script>
  function toggleDarkMode() {
    document.body.classList.toggle('dark-mode');
  }
</script>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
