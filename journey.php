<?php
require 'db.php'; // your PDO connection

// Fetch all events sorted by date ascending
$events = $pdo->query("SELECT * FROM events ORDER BY date ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Ceylon Waves - Events & Festivals</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;600;700&family=Great+Vibes&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;600&display=swap" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
  <style>
     :root {
      --primary: #7b1fa2; /* Deep Maroon */
      --secondary: #004d40; /* Dark Green */
      --accent: #f9a825; /* Golden Yellow */
      --bg: #fff8f0;
      --text: #2f2f2f;
      --card-bg: #ffffff;
      --shadow: rgba(0, 0, 0, 0.1);
    }
    body {
      margin: 0; font-family: 'Poppins', sans-serif;
      background: var(--bg);
      color: var(--text);
      line-height: 1.6;
      padding: 1rem;
    }
    /* Your original navbar styles */
    nav {
      position: fixed;
      top: 0;
      width: 100%;
      background: #fff;
      padding: 1rem 2rem;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      display: flex;
      justify-content: space-between;
      align-items: center;
      z-index: 1000;
    }
    nav a {
      margin: 0 12px;
      text-decoration: none;
      color: #555;
      transition: 0.3s;
    }
    nav a.active {
      font-weight: 700;
      color: var(--primary);
      border-bottom: 2px solid var(--primary);
    }
    .toggle-dark {
      cursor: pointer;
      font-size: 1.2rem;
      color: var(--primary);
    }
    /* Main content styling */
    main {
      max-width: 800px;
      margin: 100px auto 2rem; /* margin-top to avoid navbar */
      border-left: 4px solid var(--primary);
      padding-left: 20px;
      background: var(--bg);
    }
    h1 {
      text-align: center;
      color: var(--primary);
      margin-bottom: 2rem;
      font-weight: 600;
    }
    .event {
      background: var(--card-bg);
      border-radius: 10px;
      box-shadow: 0 2px 8px var(--shadow);
      margin-bottom: 2rem;
      padding: 1.2rem 1.5rem;
      position: relative;
      opacity: 0;
      transform: translateY(20px);
      transition: all 0.6s ease;
    }
    .event.active {
      opacity: 1;
      transform: translateY(0);
    }
    .event::before {
      content: "";
      position: absolute;
      left: -34px;
      top: 20px;
      width: 20px;
      height: 20px;
      background: var(--primary);
      border-radius: 50%;
      box-shadow: 0 0 8px var(--primary);
    }
    .event h3 {
      margin-top: 0;
      color: var(--secondary);
      font-weight: 700;
      font-size: 1.3rem;
    }
    .event .date {
      font-size: 0.9rem;
      font-weight: 600;
      color: var(--accent);
      margin-bottom: 0.8rem;
    }
    .event p {
      margin-bottom: 1rem;
      white-space: pre-line;
    }
    .ticket-info {
      background: #e0f2f1;
      border-left: 4px solid var(--accent);
      padding: 0.7rem 1rem;
      border-radius: 6px;
      font-style: italic;
      color: #004d40;
    }
    .ticket-info.price {
      background:#fff3e0;
      border-color:#ff6f00;
      color:#bf360c;
      font-style: normal;
    }
    @media(max-width:600px) {
      main {
        margin: 120px 1rem 2rem;
        padding-left: 15px;
      }
      .event::before {
        left: -28px;
        width: 16px;
        height: 16px;
        top: 16px;
      }
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
    <a href="blogs.html"  style="text-decoration: none; color: #333; font-weight: 600; position: relative;">Blogs</a>
    <a href="journey.php" class="active" style="text-decoration: none; color: #333; font-weight: 600; position: relative;">Events</a>
    <a href="Destinations&Hotels.php"  style="text-decoration: none; ; font-weight: 700; position: relative;">
      Hotels & Destinations
    </a>
    <a href="services.html" style="text-decoration: none; color: #333; font-weight: 600; position: relative;">Services</a>
    <a href="weather-news.html" style="text-decoration: none; color: #333; font-weight: 600; position: relative;">Weather & News</a>
    <a href="contact.html" style="text-decoration: none; color: #333; font-weight: 600; position: relative;">Contact</a>
  </div>
</nav>

<!-- Main Content -->
<main>
  <h1>Events & Festivals of Sri Lanka</h1>

  <?php if (count($events) === 0): ?>
    <p style="text-align:center; font-style:italic;">No events or festivals found.</p>
  <?php else: ?>
    <?php foreach ($events as $event): ?>
      <div class="event">
  <?php if (!empty($event['image'])): ?>
    <img src="<?= htmlspecialchars($event['image']) ?>" alt="<?= htmlspecialchars($event['name']) ?>" style="width:100%; max-height:300px; object-fit:cover; border-radius:10px; margin-bottom: 1rem;">
  <?php endif; ?>

  <h3><?= htmlspecialchars($event['name']) ?></h3>
  <div class="date"><?= date('F j, Y', strtotime($event['date'])) ?></div>
  <p><?= nl2br(htmlspecialchars($event['description'])) ?></p>

  <?php if (empty($event['ticket_price']) || floatval($event['ticket_price']) == 0): ?>
    <div class="ticket-info">
      Tickets not required.<br />
      To get tickets, please contact us at <strong>+94 77 123 4567</strong>.<br />
      Or book a hotel with a guide and call us with your booking reference number.<br />
      We will deliver your tickets to your hotel.
    </div>
  <?php else: ?>
    <div class="ticket-info price">
      Ticket Price: Rs <?= number_format($event['ticket_price'], 2) ?>
    </div>
  <?php endif; ?>
</div>

    <?php endforeach; ?>
  <?php endif; ?>
</main>

<script>
  // Dark Mode Toggle
  function toggleDarkMode() {
    document.body.classList.toggle('dark');
  }

  // Scroll animation for events
  window.addEventListener('scroll', () => {
    document.querySelectorAll('.event').forEach(el => {
      const top = el.getBoundingClientRect().top;
      if (top < window.innerHeight - 100) {
        el.classList.add('active');
      }
    });
  });
  window.dispatchEvent(new Event('scroll'));
</script>

</body>
</html>
