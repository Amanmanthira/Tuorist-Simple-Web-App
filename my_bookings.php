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
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>My Bookings - Explore Sri Lanka</title>

<!-- Fonts & Icons -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;600&display=swap" rel="stylesheet" />
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />

<style>
  :root {
    --primary: #2a9df4;
    --primary-dark: #217dc1;
    --bg-light: #f9fbfd;
    --card-bg: #ffffff;
    --text-dark: #222;
    --text-muted: #555;
    --shadow-light: rgba(42, 157, 244, 0.2);
    --shadow-hover: rgba(42, 157, 244, 0.35);
    --border-radius: 16px;
    --transition-speed: 0.3s;
  }

  /* Reset & basics */
  * {
    margin: 0; padding: 0; box-sizing: border-box;
    transition: background-color var(--transition-speed), color var(--transition-speed);
  }
  body {
    font-family: 'Poppins', sans-serif;
    background: var(--bg-light);
    color: var(--text-dark);
    padding-top: 80px;
    min-height: 100vh;
  }

  /* Navbar */
  nav {
    position: fixed;
    top: 0; left: 0; width: 100%;
    background: linear-gradient(to right, #ffffffcc, #ffffffdd);
    box-shadow: 0 4px 20px rgba(0,0,0,0.07);
    z-index: 999;
    padding: 1rem 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-family: 'Quicksand', sans-serif;
    backdrop-filter: blur(6px);
  }

  nav .brand {
    font-weight: 800;
    font-size: 1.6rem;
    color: var(--primary);
    display: flex;
    align-items: center;
    gap: 10px;
  }
  nav .brand img {
    width: 30px;
    border-radius: 50%;
  }
  nav .nav-links a {
    text-decoration: none;
    color: var(--text-dark);
    font-weight: 600;
    margin-left: 1.2rem;
    position: relative;
  }
  nav .nav-links a.active,
  nav .nav-links a:hover {
    color: var(--primary);
  }

  /* Heading */
  h1 {
    text-align: center;
    font-weight: 700;
    margin-bottom: 2rem;
    color: var(--primary-dark);
    font-size: 2.8rem;
  }

  /* Container for bookings */
  .bookings-container {
    max-width: 1100px;
    margin: 0 auto 4rem;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 2rem;
    padding: 0 2rem;
  }

  /* Booking card */
  .booking-card {
    background: var(--card-bg);
    border-radius: var(--border-radius);
    box-shadow: 0 8px 20px var(--shadow-light);
    padding: 1.8rem 2rem;
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
    transition: transform 0.25s ease, box-shadow 0.25s ease;
  }
  .booking-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 12px 30px var(--shadow-hover);
  }

  .booking-card h2 {
    font-weight: 700;
    font-size: 1.5rem;
    color: var(--primary);
    margin-bottom: 0.5rem;
  }

  .booking-card p {
    font-size: 0.95rem;
    color: var(--text-muted);
  }
  .booking-card p strong {
    color: var(--text-dark);
  }

  .booking-card small {
    font-size: 0.8rem;
    color: #999;
    margin-top: auto;
  }

  /* Empty state */
  .empty-message {
    max-width: 600px;
    margin: 3rem auto;
    font-size: 1.3rem;
    color: var(--text-muted);
    text-align: center;
  }
  .empty-message a {
    color: var(--primary);
    font-weight: 600;
    text-decoration: none;
  }
  .empty-message a:hover {
    text-decoration: underline;
  }

  /* Book new button */
  .center-button {
    text-align: center;
    margin-bottom: 3rem;
  }
  .booknew {
    background: var(--primary);
    color: white;
    padding: 12px 26px;
    font-weight: 600;
    font-size: 1.1rem;
    border-radius: 40px;
    box-shadow: 0 5px 18px var(--shadow-light);
    text-decoration: none;
    transition: background-color 0.3s ease, transform 0.25s ease;
    display: inline-block;
  }
  .booknew:hover {
    background: var(--primary-dark);
    transform: translateY(-3px);
    box-shadow: 0 8px 25px var(--shadow-hover);
  }

  /* Logout button */
  .btn-logout {
    position: fixed;
    bottom: 25px;
    right: 25px;
    background: var(--primary);
    color: white;
    padding: 12px 20px;
    font-weight: 600;
    border-radius: 50px;
    box-shadow: 0 6px 20px var(--shadow-light);
    text-decoration: none;
    transition: background-color 0.3s ease, transform 0.25s ease;
    z-index: 1000;
  }
  .btn-logout:hover {
    background: var(--primary-dark);
    transform: translateY(-4px);
    box-shadow: 0 10px 30px var(--shadow-hover);
  }

  /* Responsive tweaks */
  @media (max-width: 500px) {
    nav {
      padding: 1rem 1rem;
    }
    nav .nav-links {
      display: none; /* Simplify for mobile */
    }
    .bookings-container {
      padding: 0 1rem;
    }
  }

  /* Dark mode */
  body.dark-mode {
    background: #121212;
    color: #eee;
  }
  body.dark-mode nav {
    background: rgba(30,30,30,0.95);
    box-shadow: 0 4px 20px rgba(255,255,255,0.07);
  }
  body.dark-mode nav .nav-links a {
    color: #ccc;
  }
  body.dark-mode nav .nav-links a.active,
  body.dark-mode nav .nav-links a:hover {
    color: var(--primary);
  }
  body.dark-mode .booking-card {
    background: #222;
    box-shadow: 0 8px 20px rgba(0,0,0,0.6);
  }
  body.dark-mode .booking-card p strong {
    color: #eee;
  }
  body.dark-mode .booknew,
  body.dark-mode .btn-logout {
    background: var(--primary);
    box-shadow: 0 5px 18px rgba(42,157,244,0.6);
  }
  body.dark-mode .booknew:hover,
  body.dark-mode .btn-logout:hover {
    background: var(--primary-dark);
    box-shadow: 0 10px 30px rgba(42,157,244,0.8);
  }

</style>
</head>
<body>

<nav>
  <div class="brand">
    <img src="https://beyondthewesterngaze.com/wp-content/uploads/2020/08/mask-3235633_1280.jpg?w=1280" alt="Explore Sri Lanka Logo" />
    Explore Sri Lanka
  </div>
  <div class="nav-links" role="navigation" aria-label="Primary Navigation">
    <a href="index.html">Home</a>
    <a href="my_bookings.php" class="active">My Bookings</a>
    <a href="about.html">Who We Are</a>
    <a href="blogs.html">Blogs</a>
    <a href="journey.html">Journey</a>
    <a href="Destinations&Hotels.php">Hotels & Destinations</a>
    <a href="services.html">Services</a>
    <a href="weather-news.html">Weather & News</a>
    <a href="contact.html">Contact</a>
  </div>
</nav>

<h1>My Bookings</h1>

<div class="center-button">
  <a href="booking.php" class="booknew" aria-label="Book a new guide and hotel">Book New</a>
</div>

<?php if ($result && $result->num_rows > 0): ?>
  <main class="bookings-container" aria-live="polite" aria-atomic="true">
    <?php while ($booking = $result->fetch_assoc()): ?>
      <article class="booking-card" tabindex="0" aria-label="Booking reference <?= htmlspecialchars($booking['reference_number']) ?>">
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
      </article>
    <?php endwhile; ?>
  </main>
<?php else: ?>
  <p class="empty-message" role="alert">
    You have no bookings yet. <a href="booking.php">Book a guide now</a>.
  </p>
<?php endif; ?>

<a href="logout.php" class="btn-logout" aria-label="Logout from your account">Logout <i class="fa-solid fa-right-from-bracket"></i></a>

<script>
  // Dark mode toggle (optional, you can add a button later to toggle this)
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
