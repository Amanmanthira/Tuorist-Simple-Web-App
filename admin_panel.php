<?php
session_start();
require 'db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.html");
    exit;
}

// VEHICLE INSERTION LOGIC
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_vehicle'])) {
    $booking_id = $_POST['order_id'];  // rename to booking_id for clarity
    $vehicle_type = $_POST['vehicle_type'];
    $plate_number = $_POST['plate_number'];
    $driver_name = $_POST['driver_name'];

    $stmt = $pdo->prepare("INSERT INTO vehicles (booking_id, vehicle_type, plate_number, driver_name) VALUES (?, ?, ?, ?)");
    $stmt->execute([$booking_id, $vehicle_type, $plate_number, $driver_name]);
}

// Add event
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_event'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $date = $_POST['date'];
    $location = $_POST['location'];
$ticket_price = isset($_POST['ticket_price']) && $_POST['ticket_price'] !== '' ? (float)$_POST['ticket_price'] : null;

    // Handle image upload
    $imagePath = null;
    if (!empty($_FILES['image']['name'])) {
        $targetDir = "uploads/events/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);
        $imageName = time() . '_' . basename($_FILES["image"]["name"]);
        $targetFile = $targetDir . $imageName;
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
            $imagePath = $targetFile;
        }
    }

   $stmt = $pdo->prepare("INSERT INTO events (name, description, date, location, image, ticket_price) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->execute([$name, $description, $date, $location, $imagePath, $ticket_price]);
    header("Location: admin_panel.php"); // Redirect to avoid form resubmission
    exit();
}

// Delete event
if (isset($_GET['delete_event'])) {
    $id = (int)$_GET['delete_event'];
    // Optionally delete image file here
    $stmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: admin_panel.php");
    exit();
}

// Fetch events for display
$events = $pdo->query("SELECT * FROM events ORDER BY date DESC")->fetchAll();

$users = $pdo->query("SELECT id, fullname, email, username, country, created_at FROM users ORDER BY created_at DESC")->fetchAll();
$guides = $pdo->query("SELECT * FROM guides ORDER BY created_at DESC")->fetchAll();
$guide_hotel_bookings = $pdo->query("
  SELECT 
    b.id,
    b.reference_number,
    u.fullname AS user_name,
    g.name AS guide_name,
    h.name AS hotel_name,
    b.people_count,
    b.room_type,
    b.start_date,
    b.end_date,
    b.created_at
  FROM guide_hotel_bookings b
  JOIN users u ON b.user_id = u.id
  JOIN guides g ON b.guide_id = g.id
  JOIN hotels h ON b.hotel_id = h.id
  ORDER BY b.created_at DESC
")->fetchAll();
$vehicles = $pdo->query("SELECT id, vehicle_type, plate_number FROM vehicles ORDER BY vehicle_type ASC")->fetchAll();

$vehicles = $pdo->query("
    SELECT v.*, u.fullname AS user_name, g.name AS guide_name 
    FROM vehicles v 
    JOIN guide_hotel_bookings b ON v.booking_id = b.id 
    JOIN users u ON b.user_id = u.id 
    JOIN guides g ON b.guide_id = g.id 
    ORDER BY v.created_at DESC
")->fetchAll();

$available_orders = $pdo->query("
    SELECT b.id, u.fullname AS user_name, g.name AS guide_name 
    FROM guide_hotel_bookings b
    JOIN users u ON b.user_id = u.id
    JOIN guides g ON b.guide_id = g.id
    ORDER BY b.created_at DESC
")->fetchAll();

// PRODUCT INSERTION
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $product_name = $_POST['product_name'];
    $product_price = $_POST['product_price'];
    $product_description = $_POST['product_description'];

    $productImagePath = null;
    if (!empty($_FILES['product_image']['name'])) {
        $targetDir = "uploads/products/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);
        $imageName = time() . '_' . basename($_FILES["product_image"]["name"]);
        $targetFile = $targetDir . $imageName;
        if (move_uploaded_file($_FILES["product_image"]["tmp_name"], $targetFile)) {
            $productImagePath = $targetFile;
        }
    }

    $stmt = $pdo->prepare("INSERT INTO products (name, price, description, image) VALUES (?, ?, ?, ?)");
    $stmt->execute([$product_name, $product_price, $product_description, $productImagePath]);
    header("Location: admin_panel.php");
    exit;
}

// DELETE PRODUCT
if (isset($_GET['delete_product'])) {
    $id = (int)$_GET['delete_product'];
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: admin_panel.php");
    exit;
}

$products = $pdo->query("SELECT * FROM products ORDER BY created_at DESC")->fetchAll();
$product_orders = $pdo->query("
  SELECT o.*, u.fullname 
  FROM orders o
  JOIN users u ON o.user_id = u.id
  ORDER BY o.created_at DESC
")->fetchAll();


?>

?>
<?php
require 'admin_operations.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin Panel - Explore World</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet" />
  <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

  <style>
    :root {
      --primary: #0057b7;
      --accent: #007acc;
      --sidebar-border: #e0e0e0;
      --hover-bg: #f0f4f8;
      --active-bg: #e6f0ff;
      --text-color: #333;
      --icon-color: #0057b7;
      --card-bg: #fff;
      --border-color: #ddd;
      --shadow-color: rgba(0, 0, 0, 0.05);
    }

    * {
      box-sizing: border-box;
    }

    body {
      margin: 0;
      font-family: 'Inter', sans-serif;
      display: flex;
      background: #f4f6f8;
      color: var(--text-color);
      min-height: 100vh;
    }

    /* Sidebar base */
    .sidebar {
      width: 240px;
      height: 100vh;
      position: fixed;
      top: 0;
      left: 0;
      overflow: hidden;
      border-right: 1px solid var(--sidebar-border);
      box-shadow: 0 0 15px var(--shadow-color);
      z-index: 100;
      display: flex;
      flex-direction: column;
    }

    /* Slideshow background */
    .sidebar-bg-slideshow {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      z-index: 0;
      animation: slideshow 18s infinite ease-in-out;
      background-size: cover;
      background-position: center;
      opacity: 0.6;
      filter: blur(1px);
    }

    /* Content wrapper */
    .sidebar-content {
      position: relative;
      z-index: 1;
      padding: 2rem 1rem;
      backdrop-filter: blur(3px);
      background: rgba(255, 255, 255, 0.2);
      height: 100%;
      display: flex;
      flex-direction: column;
    }

    .sidebar-content h2 {
      margin: 0 0 2rem 0;
      font-size: 1.5rem;
      color: var(--primary);
      text-align: center;
      user-select: none;
    }

    .sidebar-content a {
      display: flex;
      align-items: center;
      gap: 1rem;
      padding: 0.75rem 1rem;
      margin-bottom: 0.5rem;
      text-decoration: none;
      color: var(--text-color);
      border-radius: 8px;
      font-weight: 500;
      transition: background 0.2s ease, color 0.2s ease;
      cursor: pointer;
    }

    .sidebar-content a i {
      color: var(--icon-color);
      min-width: 20px;
      text-align: center;
      font-size: 1.1rem;
    }

    .sidebar-content a:hover {
      background: var(--hover-bg);
      color: var(--primary);
    }

    .sidebar-content a.active {
      background: var(--active-bg);
      font-weight: 600;
      color: var(--primary);
    }

    .logout {
      margin-top: auto;
      background: var(--accent);
      color: white !important;
      text-align: center;
      padding: 0.5rem;
      border-radius: 6px;
      text-decoration: none !important;
      font-weight: 600;
      transition: background 0.3s ease;
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 0.5rem;
      cursor: pointer;
    }

    .logout:hover {
      background: #005fa3;
    }

    /* Main content */
    .main {
      margin-left: 240px;
      padding: 2rem;
      flex: 1;
      min-height: 100vh;
      background: #f8fafc;
    }

    .card {
      background: var(--card-bg);
      padding: 1.8rem 2rem;
      margin-bottom: 2rem;
      border-radius: 12px;
      box-shadow: 0 4px 12px var(--shadow-color);
      border: 1px solid var(--border-color);
    }

    h2 {
      margin-top: 0;
      margin-bottom: 1rem;
      color: var(--primary);
      font-weight: 600;
      font-size: 1.6rem;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      font-size: 0.95rem;
      color: var(--text-color);
    }

    th, td {
      padding: 0.75rem 1rem;
      border-bottom: 1px solid var(--border-color);
      text-align: left;
    }

    th {
      background: #f0f4f8;
      font-weight: 600;
    }

    tr:hover {
      background-color: #f5faff;
    }

    form {
      display: flex;
      gap: 0.8rem;
      flex-wrap: wrap;
      margin-bottom: 1.2rem;
    }

    form input {
      flex: 1;
      padding: 0.6rem 1rem;
      font-size: 1rem;
      border-radius: 8px;
      border: 1px solid #ccc;
      transition: border-color 0.3s;
    }

    form input:focus {
      border-color: var(--primary);
      outline: none;
    }

    form button {
      background: var(--primary);
      color: white;
      border: none;
      padding: 0 1.5rem;
      border-radius: 8px;
      cursor: pointer;
      font-weight: 600;
      transition: background 0.3s ease;
      min-width: 110px;
    }

    form button:hover {
      background: #004a9e;
    }

    @keyframes slideshow {
      0%, 100% {
        background-image: url('https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcS9dqEl3AhxE4Opc_mXhEJ5cX3K1y2sgg0n07QaShsOkOxAqNuoaP5h1ehzH2WjCGJoXQs&usqp=CAU');
      }
      33% {
        background-image: url('https://i.pinimg.com/736x/2c/03/5e/2c035e7c1ccba0e1f3bfcca10748dca5.jpg');
      }
      66% {
        background-image: url('https://i.pinimg.com/236x/5b/a9/dd/5ba9dd8c697a05978cb4e754dc86364f.jpg');
      }
    }

    @media (max-width: 768px) {
      .sidebar {
        position: fixed;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 56px;
        flex-direction: row;
        padding: 0 0.5rem;
        border-top: 1px solid var(--sidebar-border);
      }

      .sidebar-content {
        flex-direction: row;
        align-items: center;
        justify-content: space-around;
        padding: 0;
        backdrop-filter: none;
        background: rgba(255, 255, 255, 0.8);
      }

      .sidebar-content h2 {
        display: none;
      }

      .sidebar-content a {
        flex: 1;
        justify-content: center;
        padding: 0.5rem 0;
        font-size: 0.9rem;
        margin: 0;
        border-radius: 0;
      }

      .sidebar-content a i {
        font-size: 1.4rem;
        min-width: unset;
      }

      .main {
        margin-left: 0;
        padding-bottom: 70px;
      }
    }
  </style>
</head>
<body>

<div class="sidebar">
  <div class="sidebar-bg-slideshow"></div>
  <div class="sidebar-content">
    <h2>🌍 ExploreAdmin</h2>
    <a href="#" class="nav-link active" data-section="users">
      <i class="fas fa-users"></i> <span class="link-text">Users</span>
    </a>
    <a href="#" class="nav-link" data-section="guides">
      <i class="fas fa-map-signs"></i> <span class="link-text">Guides</span>
    </a>
    <a href="#" class="nav-link" data-section="orders">
      <i class="fas fa-shopping-cart"></i> <span class="link-text">Orders</span>
    </a>
    <a href="#" class="nav-link" data-section="destinations">
  <i class="fas fa-map-marker-alt"></i> <span class="link-text">Destinations</span>
</a>
<a href="#" class="nav-link" data-section="hotels">
  <i class="fas fa-hotel"></i> <span class="link-text">Hotels</span>
</a>
<a href="#" class="nav-link" data-section="events">
  <i class="fas fa-calendar-alt"></i> <span class="link-text">Events & Festivals</span>
</a>

    <a href="#" class="nav-link" data-section="vehicles">
  <i class="fas fa-car"></i> <span class="link-text">Vehicles</span>
</a>
<a href="#" class="nav-link" data-section="products">
  <i class="fas fa-product"></i> <span class="link-text">products</span>
</a>
<a href="#" class="nav-link" data-section="product_orders">
  <i class="fas fa-box"></i> <span class="link-text">Product Orders</span>
</a>

    <a href="admin_logout.php" class="logout">
      <i class="fas fa-sign-out-alt"></i> <span class="link-text">Logout</span>
    </a>
    
  </div>
</div>

<div class="main">
  <div id="users" class="section">
    <div class="card">
      <h2>Users</h2>
      <table>
        <thead>
          <tr><th>ID</th><th>Full Name</th><th>Email</th><th>Username</th><th>Country</th><th>Registered On</th></tr>
        </thead>
        <tbody>
          <?php foreach ($users as $user): ?>
          <tr>
            <td><?= htmlspecialchars($user['id']) ?></td>
            <td><?= htmlspecialchars($user['fullname']) ?></td>
            <td><?= htmlspecialchars($user['email']) ?></td>
            <td><?= htmlspecialchars($user['username']) ?></td>
            <td><?= htmlspecialchars($user['country']) ?></td>
            <td><?= htmlspecialchars($user['created_at']) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div id="guides" class="section" style="display:none;">
    <div class="card">
      <h2>Guides</h2>
      <form method="POST" action="add_guide.php">
        <input type="text" name="name" placeholder="Guide Name" required />
        <input type="text" name="language" placeholder="Languages" />
        <input type="text" name="description" placeholder="Description" />
        <button type="submit">Add Guide</button>
      </form>
      <table>
        <thead>
          <tr><th>ID</th><th>Name</th><th>Language(s)</th><th>Description</th></tr>
        </thead>
        <tbody>
          <?php foreach ($guides as $guide): ?>
          <tr>
            <td><?= htmlspecialchars($guide['id']) ?></td>
            <td><?= htmlspecialchars($guide['name']) ?></td>
            <td><?= htmlspecialchars($guide['language']) ?></td>
            <td><?= htmlspecialchars($guide['description']) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
<div id="destinations" class="section" style="display:none;">
  <div class="card">
    <h2>Add Travel Destination</h2>
    <form method="POST" enctype="multipart/form-data">
      <input type="text" name="name" placeholder="Destination Name" required />
      <input type="text" name="province" placeholder="Province (e.g., Central)" required />
      <input type="text" name="location" placeholder="Exact Location" required />
      <input type="text" name="type" placeholder="Type (Beach, Historic, etc.)" required />
      <input type="file" name="image" accept="image/*" />
      <input type="text" name="description" placeholder="Short Description" required />
      <button type="submit" name="add_destination">Add Destination</button>
    </form>

    <table>
      <thead>
        <tr>
          <th>ID</th><th>Name</th><th>Province</th><th>Location</th><th>Type</th><th>Image</th><th>Description</th>
        </tr>
      </thead>
      <tbody>
  <?php foreach ($destinations as $d): ?>
  <tr>
    <td><?= htmlspecialchars($d['id']) ?></td>
    <td><?= htmlspecialchars($d['name']) ?></td>
    <td><?= htmlspecialchars($d['province']) ?></td>
    <td><?= htmlspecialchars($d['location']) ?></td>
    <td><?= htmlspecialchars($d['type']) ?></td>
    <td>
      <?php if ($d['image']): ?>
        <img src="<?= htmlspecialchars($d['image']) ?>" width="60" style="border-radius: 4px;" />
      <?php else: ?>N/A<?php endif; ?>
    </td>
    <td><?= htmlspecialchars($d['description']) ?></td>
    <td>
      <a href="edit_destination.php?id=<?= $d['id'] ?>" style="color: #007acc; font-weight: bold;">✏️ Edit</a> |
      <a href="delete_destination.php?id=<?= $d['id'] ?>" onclick="return confirm('Are you sure to delete this destination?')" style="color: red; font-weight: bold;">🗑️ Delete</a>
    </td>
  </tr>
  <?php endforeach; ?>
</tbody>

    </table>
  </div>
</div>

<div id="hotels" class="section" style="display:none;">
  <div class="card">
    <h2>Add Hotel</h2>
    <form method="POST" enctype="multipart/form-data">
  <input type="text" name="name" placeholder="Hotel Name" required />
  <input type="text" name="province" placeholder="Province" required />
  <input type="text" name="location" placeholder="Location" required />
  <input type="number" name="stars" placeholder="Stars (1-5)" min="1" max="5" required />
  <input type="number" name="price_min" placeholder="Min Price Per Day (With Food)" required />
  <input type="number" name="price_max" placeholder="Max Price Per Day (With Food)" required />
  <input type="file" name="image" accept="image/*" />
  <input type="text" name="description" placeholder="Description" required />
  <button type="submit" name="add_hotel">Add Hotel</button>
</form>


    <table>
      <thead>
        <tr>
          <th>ID</th><th>Name</th><th>Province</th><th>Location</th><th>Stars</th><th>Min Price</th><th>Max Price</th><th>Image</th><th>Description</th><th>Actions</th>

        </tr>
      </thead>
      <tbody>
        <?php foreach ($hotels as $hotel): ?>
        <tr>
          <td><?= htmlspecialchars($hotel['id']) ?></td>
          <td><?= htmlspecialchars($hotel['name']) ?></td>
          <td><?= htmlspecialchars($hotel['province']) ?></td>
          <td><?= htmlspecialchars($hotel['location']) ?></td>
          <td><?= htmlspecialchars($hotel['stars']) ?></td>
          <td>Rs <?= htmlspecialchars($hotel['price_min']) ?></td>
          <td>Rs <?= htmlspecialchars($hotel['price_max']) ?></td>
          <td>
            <?php if ($hotel['image']): ?>
              <img src="<?= htmlspecialchars($hotel['image']) ?>" width="60" style="border-radius: 4px;" />
            <?php else: ?>N/A<?php endif; ?>
          </td>
          <td><?= htmlspecialchars($hotel['description']) ?></td>
          <td>
            <a href="edit_hotel.php?id=<?= $hotel['id'] ?>" style="color: #007acc; font-weight: bold;">✏️ Edit</a> | 
            <a href="delete_hotel.php?id=<?= $hotel['id'] ?>" onclick="return confirm('Are you sure to delete this hotel?')" style="color: red; font-weight: bold;">🗑️ Delete</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Inside your HTML where admin panels are -->
<div id="products" class="section" style="display:none;">
  <div class="card">
    <h2>Manage Products</h2>
    <form method="POST" enctype="multipart/form-data">
      <input type="text" name="product_name" placeholder="Product Name" required />
      <input type="number" step="0.01" name="product_price" placeholder="Price (LKR)" required />
      <textarea name="product_description" placeholder="Short Description" rows="2"></textarea>
      <input type="file" name="product_image" accept="image/*" />
      <button type="submit" name="add_product">Add Product</button>
    </form>

    <table>
      <thead>
        <tr><th>ID</th><th>Name</th><th>Price</th><th>Description</th><th>Image</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <?php foreach ($products as $product): ?>
        <tr>
          <td><?= htmlspecialchars($product['id']) ?></td>
          <td><?= htmlspecialchars($product['name']) ?></td>
          <td>Rs <?= htmlspecialchars($product['price']) ?></td>
          <td><?= htmlspecialchars($product['description']) ?></td>
          <td>
            <?php if ($product['image']): ?>
              <img src="<?= htmlspecialchars($product['image']) ?>" width="60" style="border-radius:4px;" />
            <?php else: ?>N/A<?php endif; ?>
          </td>
          <td>
            <a href="edit_product.php?id=<?= $product['id'] ?>" style="color: #007acc; font-weight: bold;">✏️ Edit</a> |
            <a href="?delete_product=<?= $product['id'] ?>" onclick="return confirm('Delete this product?')" style="color: red; font-weight: bold;">🗑️ Delete</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<div id="product_orders" class="section" style="display:none;">
  <div class="card">
    <h2>Product Order History</h2>
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>User</th>
          <th>Product</th>
          <th>Qty</th>
          <th>Price (LKR)</th>
          <th>Reference #</th>
          <th>Ordered On</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($product_orders as $order): ?>
        <tr>
          <td><?= htmlspecialchars($order['id']) ?></td>
          <td><?= htmlspecialchars($order['fullname']) ?></td>
          <td><?= htmlspecialchars($order['product_name']) ?></td>
          <td><?= htmlspecialchars($order['quantity']) ?></td>
          <td>Rs <?= number_format($order['price'], 2) ?></td>
          <td><?= htmlspecialchars($order['reference_number']) ?></td>
          <td><?= htmlspecialchars($order['created_at']) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<div id="events" class="section" style="display:none;">
  <div class="card">
    <h2>Add New Event or Festival</h2>
    <form method="POST" enctype="multipart/form-data">
      <input type="text" name="name" placeholder="Event Name" required />
      <input type="date" name="date" required />
      <input type="text" name="location" placeholder="Location" required />
      <textarea name="description" placeholder="Description" rows="3"></textarea>
      <input type="number" step="0.01" min="0" name="ticket_price" placeholder="Ticket Price (0 if no ticket)" />
      <input type="file" name="image" accept="image/*" />
      <button type="submit" name="add_event">Add Event</button>
    </form>

    <h2>Existing Events & Festivals</h2>
    <table>
      <thead>
        <tr>
          <th>ID</th><th>Name</th><th>Date</th><th>Location</th><th>Description</th><th>Image</th><th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($events as $event): ?>
          <tr>
            <td><?= htmlspecialchars($event['id']) ?></td>
            <td><?= htmlspecialchars($event['name']) ?></td>
            <td><?= htmlspecialchars($event['date']) ?></td>
            <td><?= htmlspecialchars($event['location']) ?></td>
            <td><?= nl2br(htmlspecialchars($event['description'])) ?></td>
            <td>
              <?php if ($event['image']): ?>
                <img src="<?= htmlspecialchars($event['image']) ?>" width="80" style="border-radius: 6px;" />
              <?php else: ?>N/A<?php endif; ?>
            </td>
            <td>
  <?php 
    if (is_null($event['ticket_price']) || $event['ticket_price'] == 0): 
  ?>
    <em>Tickets not required.</em><br />
    <small>
      To get tickets, please contact us at <strong>+94 77 123 4567</strong>.<br />
      Or book a hotel with a guide and call us with your booking reference number.<br />
      We will deliver your tickets to your hotel.
    </small>
  <?php else: ?>
    Rs <?= number_format($event['ticket_price'], 2) ?>
  <?php endif; ?>
</td>

            <td>
              <!-- You can add edit link here if needed -->
              <a href="?delete_event=<?= $event['id'] ?>" onclick="return confirm('Delete this event?')" style="color:red; font-weight:bold;">🗑️ Delete</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

  <div id="orders" class="section" style="display:none;">
    <div class="card">
      <h2>Orders / Bookings</h2>
      <table>
        <thead>
  <tr>
    <th>ID</th>
     <th>Reference</th>
    <th>User</th>
    <th>Guide</th>
    <th>Hotel</th>
    <th>People</th>
    <th>Room Type</th>
    <th>Start Date</th>
    <th>End Date</th>
    <th>Booked On</th>
  </tr>
</thead>
<tbody>
  <?php foreach ($guide_hotel_bookings as $b): ?>
  <tr>
    <td><?= htmlspecialchars($b['id']) ?></td>
    <td><?= htmlspecialchars($b['reference_number']) ?></td>
    <td><?= htmlspecialchars($b['user_name']) ?></td>
    <td><?= htmlspecialchars($b['guide_name']) ?></td>
    <td><?= htmlspecialchars($b['hotel_name']) ?></td>
    <td><?= htmlspecialchars($b['people_count']) ?></td>
    <td><?= ucfirst($b['room_type']) ?></td>
    <td><?= htmlspecialchars($b['start_date']) ?></td>
    <td><?= htmlspecialchars($b['end_date']) ?></td>
    <td><?= htmlspecialchars($b['created_at']) ?></td>
  </tr>
  <?php endforeach; ?>
</tbody>

      </table>
    </div>
  </div>
<div id="vehicles" class="section" style="display:none;">
  <div class="card">
    <h2>Assign Vehicles to Bookings</h2>
    <form method="POST">
      <select name="order_id" required>
        <option value="">Select Booking</option>
        <?php foreach ($available_orders as $order): ?>
          <option value="<?= $order['id'] ?>">
            <?= htmlspecialchars($order['user_name']) ?> - <?= htmlspecialchars($order['guide_name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
      <input type="text" name="vehicle_type" placeholder="Vehicle Type" required />
      <input type="text" name="plate_number" placeholder="Plate Number" required />
      <input type="text" name="driver_name" placeholder="Driver Name" required />
      <button type="submit" name="add_vehicle">Assign Vehicle</button>
    </form>

    <table>
      <thead>
        <tr>
          <th>ID</th><th>User</th><th>Guide</th><th>Vehicle</th><th>Plate</th><th>Driver</th><th>Assigned On</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($vehicles as $v): ?>
        <tr>
          <td><?= htmlspecialchars($v['id']) ?></td>
          <td><?= htmlspecialchars($v['user_name']) ?></td>
          <td><?= htmlspecialchars($v['guide_name']) ?></td>
          <td><?= htmlspecialchars($v['vehicle_type']) ?></td>
          <td><?= htmlspecialchars($v['plate_number']) ?></td>
          <td><?= htmlspecialchars($v['driver_name']) ?></td>
          <td><?= htmlspecialchars($v['created_at']) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

</div>

<script>
  const links = document.querySelectorAll('.nav-link');
  const sections = document.querySelectorAll('.section');

  links.forEach(link => {
    link.addEventListener('click', e => {
      e.preventDefault();
      links.forEach(l => l.classList.remove('active'));
      link.classList.add('active');
      const target = link.getAttribute('data-section');
      sections.forEach(section => {
        section.style.display = (section.id === target) ? 'block' : 'none';
      });
    });
  });
</script>
</body>
</html>
