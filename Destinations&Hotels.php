<?php
require 'db.php';

// Handle search input
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$hotelQuery = "SELECT * FROM hotels";
$destinationQuery = "SELECT * FROM destinations";

if ($search !== '') {
  $hotelQuery .= " WHERE name LIKE ? OR province LIKE ?";
  $destinationQuery .= " WHERE name LIKE ? OR province LIKE ? OR type LIKE ?";
  
  $hotels = $pdo->prepare($hotelQuery);
  $hotels->execute(["%$search%", "%$search%"]);

  $destinations = $pdo->prepare($destinationQuery);
  $destinations->execute(["%$search%", "%$search%", "%$search%"]);
} else {
  $hotels = $pdo->query("$hotelQuery ORDER BY created_at DESC")->fetchAll();
  $destinations = $pdo->query("$destinationQuery ORDER BY created_at DESC")->fetchAll();
}

if (!isset($hotels) || !is_array($hotels)) {
  $hotels = $hotels->fetchAll();
}
if (!isset($destinations) || !is_array($destinations)) {
  $destinations = $destinations->fetchAll();
}
$hotels = $pdo->query("SELECT * FROM hotels ORDER BY created_at DESC")->fetchAll();
$destinations = $pdo->query("SELECT * FROM destinations ORDER BY created_at DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Explore Destinations & Hotels</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
   <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;600;700&family=Great+Vibes&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;600&display=swap" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
  <style>
    :root {
      --primary-light: #3399ff;
      --primary: #0066cc;
      --primary-dark: #004a99;
      --bg-light: #f9faff;
      --card-bg: rgba(255, 255, 255, 0.9);
      --shadow-primary: rgba(0, 102, 204, 0.25);
      --font-family: 'Poppins', sans-serif;
      --transition: 0.4s ease;
      --border-radius: 18px;
    }

    * {
      box-sizing: border-box;
    }

    body {
      margin: 0;
      font-family: var(--font-family);
      background: var(--bg-light);
      color: #222;
      min-height: 100vh;
    }


    main {
      max-width: 1200px;
      margin: 7rem auto 4rem;
      padding: 0 2rem;
    }

    h1 {
      font-weight: 800;
      font-size: 3.4rem;
      text-align: center;
      margin-bottom: 3.5rem;
      color: var(--primary);
      text-transform: uppercase;
    }

    #tabs {
      display: flex;
      justify-content: center;
      gap: 2.5rem;
      margin-bottom: 3rem;
    }

    .tab-btn {
      background: transparent;
      border: 2.5px solid var(--primary);
      padding: 0.65rem 2.2rem;
      border-radius: 50px;
      font-weight: 700;
      font-size: 1.25rem;
      color: var(--primary);
      cursor: pointer;
      transition: var(--transition);
    }

    .tab-btn:hover {
      color: var(--primary-dark);
      border-color: var(--primary-dark);
      box-shadow: 0 0 16px var(--shadow-primary);
    }

    .tab-btn.active {
      color: white;
      background: var(--primary);
      border-color: var(--primary);
      box-shadow: 0 0 18px var(--shadow-primary);
    }

    .tab-content {
      display: none;
      animation: fadeInUp 0.5s ease forwards;
    }

    .tab-content.active {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(340px, 1fr));
      gap: 2.7rem;
    }

    .card {
      border-radius: var(--border-radius);
      overflow: hidden;
      box-shadow: 0 10px 20px var(--shadow-primary);
      background: var(--card-bg);
      display: flex;
      flex-direction: column;
      height: 100%;
      transition: transform 0.3s ease;
    }

    .card:hover {
      transform: translateY(-8px) scale(1.01);
    }

    .card img {
      width: 100%;
      height: 220px;
      object-fit: cover;
    }

    .card-info {
      padding: 1rem 1.5rem;
      font-weight: 700;
      font-size: 1.3rem;
      color: var(--primary);
      background: linear-gradient(180deg, transparent 0%, rgba(255 255 255 / 0.95) 90%);
    }

    .card-content {
      padding: 1.6rem 2rem 2rem;
      display: flex;
      flex-direction: column;
    }

    .card-content h3 {
      margin: 0 0 0.3rem 0;
      font-weight: 800;
      font-size: 1.45rem;
      color: var(--primary-dark);
    }

    .card-content p {
      font-size: 1rem;
      line-height: 1.5;
      color: #444;
      margin-bottom: 1.5rem;
    }

    .card-content button {
      background: var(--primary);
      border: none;
      padding: 0.75rem 2rem;
      font-weight: 700;
      font-size: 1.1rem;
      color: white;
      border-radius: 40px;
      cursor: pointer;
      box-shadow: 0 5px 15px var(--shadow-primary);
    }

    .card-content button:hover {
      background: var(--primary-dark);
    }

    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(25px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
     @keyframes fireGlow {
  0% {
    filter: drop-shadow(0 0 4px rgba(255, 140, 0, 0.6));
  }
  50% {
    filter: drop-shadow(0 0 12px rgba(255, 190, 0, 1));
  }
  100% {
    filter: drop-shadow(0 0 6px rgba(255, 140, 0, 0.8));
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
  background: linear-gradient(to right, #ffffffcc, #ffffffdd),  repeat;
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
    <a href="journey.php" class="active" style="text-decoration: none; color: #333; font-weight: 600; position: relative;">Journey</a>
    <a href="Destinations&Hotels.php"  style="text-decoration: none; ; font-weight: 700; position: relative;">
      Hotels & Destinations
    </a>
    <a href="services.html" style="text-decoration: none; color: #333; font-weight: 600; position: relative;">Services</a>
    <a href="weather-news.html" style="text-decoration: none; color: #333; font-weight: 600; position: relative;">Weather & News</a>
    <a href="contact.html" style="text-decoration: none; color: #333; font-weight: 600; position: relative;">Contact</a>
  </div>
</nav>

<main>
  <h1>Explore Destinations & Hotels</h1>

  <form method="get" style="max-width:600px;margin:0 auto 2rem;display:flex;gap:1rem;">
    <input type="text" name="search" placeholder="Search by name, province or type..." value="<?= htmlspecialchars($search) ?>" style="flex:1;padding:0.75rem 1rem;border:2px solid var(--primary);border-radius:50px;font-size:1rem;">
    <button type="submit" style="padding:0.75rem 1.5rem;background:var(--primary);color:#fff;border:none;border-radius:50px;font-weight:600;cursor:pointer;">Search</button>
  </form>

  <div id="tabs">
    <button class="tab-btn active" data-target="destinations">Destinations</button>
    <button class="tab-btn" data-target="hotels">Hotels</button>
  </div>

  <!-- Destinations Tab -->
  <section id="destinations" class="tab-content active">
    <?php if (count($destinations) === 0): ?>
      <p style="text-align:center; font-size:1.3rem; color:#999;">No destinations found.</p>
    <?php else: ?>
      <?php foreach ($destinations as $d): ?>
        <article class="card" tabindex="0" aria-label="Destination: <?= htmlspecialchars($d['name']) ?>">
          <img src="<?= file_exists($d['image']) ? htmlspecialchars($d['image']) : 'https://via.placeholder.com/400x230?text=No+Image' ?>" alt="<?= htmlspecialchars($d['name']) ?>" loading="lazy">
          <div class="card-info"><?= htmlspecialchars($d['name']) ?></div>
          <div class="card-content">
            <h3><?= htmlspecialchars($d['province']) ?> | <?= htmlspecialchars($d['type']) ?></h3>
            <p><?= htmlspecialchars($d['description']) ?></p>
            <button onclick="alert('Explore destination: <?= htmlspecialchars(addslashes($d['name'])) ?>')">Explore</button>
          </div>
        </article>
      <?php endforeach; ?>
    <?php endif; ?>
  </section>

  <!-- Hotels Tab -->
  <section id="hotels" class="tab-content">
    <?php if (count($hotels) === 0): ?>
      <p style="text-align:center; font-size:1.3rem; color:#999;">No hotels found.</p>
    <?php else: ?>
      <?php foreach ($hotels as $h): ?>
        <article class="card" tabindex="0" aria-label="Hotel: <?= htmlspecialchars($h['name']) ?>">
          <img src="<?= file_exists($h['image']) ? htmlspecialchars($h['image']) : 'https://via.placeholder.com/400x230?text=No+Image' ?>" alt="<?= htmlspecialchars($h['name']) ?>" loading="lazy">
          <div class="card-info"><?= htmlspecialchars($h['name']) ?></div>
          <div class="card-content">
            <h3><?= htmlspecialchars($h['province']) ?> | ⭐ <?= intval($h['stars']) ?> Stars</h3>
           <p><?= htmlspecialchars($h['description']) ?></p>

<?php if (isset($h['price_min']) && isset($h['price_max'])): ?>
  <p style="font-weight: 600; color: #0066cc; margin-top: -0.5rem;">
    Normal Room: Rs. <?= number_format($h['price_min']) ?> |
    Luxury Room: Rs. <?= number_format($h['price_max']) ?><br>
    <small style="font-weight: normal; color: #555;">(with all foods: breakfast, lunch, dinner per day)</small>
  </p>
<?php endif; ?>

<button onclick="alert('Book hotel: <?= htmlspecialchars(addslashes($h['name'])) ?>')">Book Now</button>

          </div>
        </article>
      <?php endforeach; ?>
    <?php endif; ?>
  </section>
</main>

<!-- Fire GIF that follows the mouse -->
<img id="fire-cursor" src="images/fire.gif" 
     alt="fire cursor" 
  style="
       position: fixed; 
       top: 0; 
       left: 0; 
       width: 90px; 
       height: 90px; 
       pointer-events: none; 
       z-index: 1500; 
       user-select: none;
       filter: drop-shadow(0 0 6px rgba(255, 140, 0, 0.8));
       animation: fireGlow 2s ease-in-out infinite alternate;
     ">
    <script>
  const fireCursor = document.getElementById('fire-cursor');

  window.addEventListener('mousemove', e => {
    const offsetX = 20;  // Adjust so fire doesn't block the arrow tip
    const offsetY = 20;
    fireCursor.style.left = (e.clientX + offsetX) + 'px';
    fireCursor.style.top = (e.clientY + offsetY) + 'px';
  });
</script>

<script>
  const tabs = document.querySelectorAll('.tab-btn');
  const contents = document.querySelectorAll('.tab-content');

  tabs.forEach(tab => {
    tab.addEventListener('click', () => {
      tabs.forEach(t => t.classList.remove('active'));
      tab.classList.add('active');

      contents.forEach(c => c.classList.remove('active'));
      const target = tab.getAttribute('data-target');
      document.getElementById(target).classList.add('active');
    });
  });
</script>

</body>
</html>
