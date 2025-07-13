<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: login.html");
  exit();
}

$user_id = $_SESSION['user_id'];

$booking = $pdo->prepare("SELECT reference_number FROM guide_hotel_bookings WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
$booking->execute([$user_id]);
$booking_data = $booking->fetch();
$ref_number = $booking_data ? $booking_data['reference_number'] : null;

$products = $pdo->query("SELECT * FROM products ORDER BY created_at DESC")->fetchAll();

function getRandomProducts($products, $count = 6) {
  shuffle($products);
  return array_slice($products, 0, $count);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Ceylon Market</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
   <a href="cart.php" 
     style="text-decoration: none; background: #1e88e5; color: white; padding: 14px 24px; border-radius: 8px; font-weight: 600; font-size: 16px; display: inline-block; transition: background-color 0.3s ease; margin: 10px 10px 0 0;">
    🧺 Cart
  </a>

  <button onclick="toggleHistory()" 
          style="background: #1e88e5; color: white; padding: 14px 24px; font-size: 16px; font-weight: 600; border: none; border-radius: 8px; cursor: pointer; transition: background-color 0.3s ease; display: inline-block;">
    🧾 View My Order History
  </button>

  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: #f0f2f5;
      margin: 0;
      color: #333;
    }
    header {
      background: #ffffff;
      padding: 1rem 2rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
      border-bottom: 1px solid #ddd;
      box-shadow: 0 4px 12px rgba(0,0,0,0.04);
    }
    header h1 {
      color: #1e88e5;
      font-size: 1.8rem;
    }
    .ref-code {
      background: #e3f2fd;
      padding: 0.5rem 1rem;
      border-radius: 8px;
      font-weight: 600;
      color: #0d47a1;
    }

    section {
      padding: 2rem 3%;
    }
    section h2 {
      margin-bottom: 1rem;
      color: #222;
      font-size: 1.4rem;
    }

    .carousel-wrapper {
      position: relative;
      overflow: hidden;
    }
    .products-row {
      display: flex;
      gap: 1.5rem;
      transition: transform 0.6s ease-in-out;
      will-change: transform;
    }

    .product-card {
      flex: 0 0 auto;
      width: 240px;
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 8px 24px rgba(0,0,0,0.06);
      overflow: hidden;
      transition: transform 0.3s;
    }
    .product-card:hover {
      transform: translateY(-5px);
    }
    .product-card img {
      width: 100%;
      height: 160px;
      object-fit: cover;
    }
    .product-details {
      padding: 1rem;
      position: relative;
    }
    .product-details h3 {
      font-size: 1.05rem;
      margin-bottom: 0.4rem;
    }
    .product-details p {
      font-size: 0.88rem;
      color: #666;
      height: 38px;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    .product-details span.price {
      display: block;
      margin-top: 0.6rem;
      color: #2e7d32;
      font-weight: bold;
      font-size: 1rem;
    }
    .product-details .badge {
      position: absolute;
      top: -10px;
      right: -10px;
      background: #ff5252;
      color: #fff;
      padding: 0.3rem 0.6rem;
      font-size: 0.75rem;
      border-radius: 6px;
      font-weight: 600;
    }
    .product-details form button {
      margin-top: 0.7rem;
      background: #1e88e5;
      color: white;
      padding: 10px 14px;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-weight: 600;
      width: 100%;
      transition: background 0.3s;
    }
    .product-details form button:hover {
      background: #1565c0;
    }

    @media (max-width: 600px) {
      .product-card {
        width: 180px;
      }
    }
    .hero-slider {
    position: relative;
    width: 100%;
    height: 360px;
    overflow: hidden;
    margin-bottom: 2rem;
  }
  .hero-slide {
    position: absolute;
    width: 100%;
    height: 100%;
    object-fit: cover;
    opacity: 0;
    transition: opacity 1s ease-in-out;
  }
  .hero-slide.active {
    opacity: 1;
    z-index: 2;
  }
  .hero-caption {
    position: absolute;
    bottom: 40px;
    left: 60px;
    color: #fff;
    background: rgba(0,0,0,0.5);
    padding: 1rem 2rem;
    border-radius: 8px;
    max-width: 50%;
    font-size: 1.5rem;
    font-weight: 600;
    box-shadow: 0 6px 16px rgba(0,0,0,0.2);
  }
  @media (max-width: 768px) {
    .hero-caption {
      font-size: 1rem;
      bottom: 20px;
      left: 20px;
      max-width: 80%;
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
    <img src="https://beyondthewesterngaze.com/wp-content/uploads/2020/08/mask-3235633_1280.jpg?w=1280" alt="logo" style="width:30px; margin-right:10px;"> Explore SriLanka
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
 <!-- Buttons Section (below navbar) -->
<div style="margin-top: 10px; padding: 1rem ; display: flex; gap: 1rem; flex-wrap: wrap;">
  <a href="cart.php" 
     style="text-decoration: none; background: #1e88e5; color: white; padding: 14px 24px; border-radius: 8px; font-weight: 600; font-size: 16px; display: inline-block; transition: background-color 0.3s ease;">
    🧺 Cart
  </a>

  <button onclick="toggleHistory()" 
          style="background: #1e88e5; color: white; padding: 14px 24px; font-size: 16px; font-weight: 600; border: none; border-radius: 8px; cursor: pointer; transition: background-color 0.3s ease;">
    🧾 View My Order History
  </button>
</div>

<header>
  <h1>Ceylon Market</h1>
  <div class="ref-code">
    <?= $ref_number ? "Booking Ref: " . htmlspecialchars($ref_number) : "No recent booking" ?>
  </div>
</header>
<div class="hero-slider" id="heroSlider">
  <img src="https://www.tentmaster.lk/wp-content/uploads/2021/04/slider-image-1-3-min.jpg" alt="Slide 1" class="hero-slide active">
  <img src="https://www.tentmaster.lk/wp-content/uploads/2021/04/Slider-images-2-2-min.jpg" alt="Slide 2" class="hero-slide">
  <img src="https://www.tentmaster.lk/wp-content/uploads/2021/04/slider-images-3-2-min-1.jpg" alt="Slide 3" class="hero-slide">
  <div class="hero-caption">🌴 Discover the Best of Ceylon – Fresh, Local & Delivered</div>
</div>

<?php
function renderSection($title, $products, $ref_number) {
  echo "<section><h2>$title</h2><div class='carousel-wrapper'><div class='products-row'>";
  foreach ($products as $product): 
    $badge = rand(0, 1) ? "New" : "Hot"; ?>
    <div class="product-card">
      <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
      <div class="product-details">
        <div class="badge"><?= $badge ?></div>
        <h3><?= htmlspecialchars($product['name']) ?></h3>
        <p><?= htmlspecialchars($product['description']) ?></p>
        <span class="price">Rs <?= number_format($product['price'], 2) ?></span>
        <form action="add_to_cart.php" method="POST">
          <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
          <input type="hidden" name="reference_number" value="<?= $ref_number ?>">
          <button type="submit"><i class="fas fa-cart-plus"></i> Add to Cart</button>
        </form>
      </div>
    </div>
  <?php endforeach;
  echo "</div></div></section>";
}

renderSection("🆕 New Arrivals", getRandomProducts($products), $ref_number);
renderSection("🔥 Top Sales", getRandomProducts($products), $ref_number);
renderSection("💥 Hot Deals", getRandomProducts($products), $ref_number);
?>


<?php
// Fetch shopping history grouped by reference_number
$orders_stmt = $pdo->prepare("
  SELECT reference_number, MIN(created_at) AS order_date
  FROM orders
  WHERE user_id = ?
  GROUP BY reference_number
  ORDER BY order_date DESC
");
$orders_stmt->execute([$user_id]);
$orders = $orders_stmt->fetchAll();
?>

<div id="historySection" style="display:none;">

<section>
  <h2>🧾 Your Shopping History</h2>

  <?php if (empty($orders)): ?>
    <p>You have no past orders.</p>
  <?php else: ?>
    <?php foreach ($orders as $order): ?>
      <div style="background:#fff; padding:1rem; margin-bottom:1rem; border-radius:8px; box-shadow:0 4px 12px rgba(0,0,0,0.05);">
        <p><strong>Reference Number:</strong> <?= htmlspecialchars($order['reference_number']) ?></p>
        <p><strong>Date:</strong> <?= date('M d, Y H:i', strtotime($order['order_date'])) ?></p>

        <?php
        $items_stmt = $pdo->prepare("
          SELECT product_name, quantity, price
          FROM orders
          WHERE reference_number = ?
        ");
        $items_stmt->execute([$order['reference_number']]);
        $items = $items_stmt->fetchAll();

        $total = 0;
        foreach ($items as $item) {
          $total += $item['price'] * $item['quantity'];
        }
        ?>

        <ul>
          <?php foreach ($items as $item): ?>
            <li>
              <?= htmlspecialchars($item['product_name']) ?> — Qty: <?= $item['quantity'] ?> — Rs <?= number_format($item['price'], 2) ?>
            </li>
          <?php endforeach; ?>
        </ul>

        <p><strong>Total:</strong> Rs <?= number_format($total, 2) ?></p>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</section>
 </div>


<script>
  document.addEventListener("DOMContentLoaded", () => {
    const carousels = document.querySelectorAll('.carousel-wrapper');

    carousels.forEach(wrapper => {
      const row = wrapper.querySelector('.products-row');
      let scrollAmount = 0;
      const cardWidth = row.querySelector('.product-card').offsetWidth + 24;

      function autoSlide() {
        if (wrapper.matches(':hover')) return;

        scrollAmount += cardWidth;
        if (scrollAmount >= row.scrollWidth - wrapper.offsetWidth) {
          scrollAmount = 0;
        }

        row.scrollTo({
          left: scrollAmount,
          behavior: 'smooth'
        });
      }

      setInterval(autoSlide, 3000);
    });
  });


  document.addEventListener("DOMContentLoaded", () => {
    const slides = document.querySelectorAll('.hero-slide');
    let currentIndex = 0;

    function showSlide(index) {
      slides.forEach((slide, i) => {
        slide.classList.toggle('active', i === index);
      });
    }

    function nextSlide() {
      currentIndex = (currentIndex + 1) % slides.length;
      showSlide(currentIndex);
    }

    setInterval(nextSlide, 5000); // Change every 5 seconds
  });


  function toggleHistory() {
  const historyDiv = document.getElementById("historySection");
  if (historyDiv.style.display === "none") {
    historyDiv.style.display = "block";
    window.scrollTo({
      top: historyDiv.offsetTop - 20,
      behavior: 'smooth'
    });
  } else {
    historyDiv.style.display = "none";
  }
}

</script>

</body>
</html>
