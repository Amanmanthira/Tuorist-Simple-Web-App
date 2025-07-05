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
    SELECT 
      b.id,
      b.booking_date,
      b.tour_type,
      b.room_type,
      b.start_date,
      b.end_date,
      b.people_count,
      b.days,
      b.created_at,
      b.reference_number,          
      g.name AS guide_name,
      g.language,
      h.name AS hotel_name,
      h.location AS hotel_location
    FROM guide_hotel_bookings b
    JOIN guides g ON b.guide_id = g.id
    JOIN hotels h ON b.hotel_id = h.id
    WHERE b.user_id = ?
    ORDER BY b.created_at DESC
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
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;600;700&family=Great+Vibes&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
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
<!-- Navbar -->
  <nav style="
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  background: linear-gradient(to right, #ffffffcc, #ffffffdd), url('https://beyondthewesterngaze.com/wp-content/uploads/2020/08/mask-3235633_1280.jpg?w=1280') repeat;
  background-size: 200px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.07);
  z-index: 999;
  padding: 1rem 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-family: 'Quicksand', sans-serif;
  backdrop-filter: blur(6px);
">

  <!-- Logo / Brand -->
  <div style="font-weight: 800; font-size: 1.6rem; color: var(--primary); display: flex; align-items: center;">
    <img src="https://beyondthewesterngaze.com/wp-content/uploads/2020/08/mask-3235633_1280.jpg?w=1280" alt="logo" style="width:30px; margin-right:10px;"> Explore Srilanka
  </div>

  <!-- Links -->
  <div class="nav-links" style="display: flex; gap: 1.2rem;">
    <a href="index.html" style="text-decoration: none; color: #333; font-weight: 600; position: relative; transition: color 0.3s;">
      Home
    </a>
    <a href="http://localhost/project/my_bookings.php" style="text-decoration: none; color: #333; font-weight: 600; position: relative;">My Bookings</a>
    <a href="about.html" style="text-decoration: none; color: #333; font-weight: 600; position: relative;">Who We Are</a>
    <a href="blogs.html" style="text-decoration: none; color: #333; font-weight: 600; position: relative;">Blogs</a>
    <a href="journey.html" style="text-decoration: none; color: #333; font-weight: 600; position: relative;">Journey</a>
    <a href="Destinations&Hotels.php" class="active" style="text-decoration: none; color: var(--primary); font-weight: 700; position: relative;">
      Hotels & Destinations
    </a>
    <a href="services.html" style="text-decoration: none; color: #333; font-weight: 600; position: relative;">Services</a>
    <a href="weather-news.html" style="text-decoration: none; color: #333; font-weight: 600; position: relative;">Weather & News</a>
    <a href="contact.html" style="text-decoration: none; color: #333; font-weight: 600; position: relative;">Contact</a>
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
        <h2>Guide: <?= htmlspecialchars($booking['guide_name']) ?></h2>
          <p><strong>Reference No:</strong> <?= htmlspecialchars($booking['reference_number']) ?></p>
        <p><strong>Language:</strong> <?= htmlspecialchars($booking['language']) ?></p>
        <p><strong>Hotel:</strong> <?= htmlspecialchars($booking['hotel_name']) ?> (<?= htmlspecialchars($booking['hotel_location']) ?>)</p>
        <p><strong>Room Type:</strong> <?= htmlspecialchars($booking['room_type']) ?></p>
        <p><strong>Tour Type:</strong> <?= htmlspecialchars(ucfirst($booking['tour_type'])) ?></p>
        <p><strong>Start Date:</strong> <?= htmlspecialchars($booking['start_date']) ?></p>
        <p><strong>End Date:</strong> <?= htmlspecialchars($booking['end_date']) ?></p>
        <p><strong>Days:</strong> <?= htmlspecialchars($booking['days']) ?></p>
        <p><strong>People:</strong> <?= htmlspecialchars($booking['people_count']) ?></p>
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
