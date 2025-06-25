<?php
session_start();
require 'db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.html");
    exit;
}

// VEHICLE INSERTION LOGIC
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_vehicle'])) {
    $order_id = $_POST['order_id'];
    $vehicle_type = $_POST['vehicle_type'];
    $plate_number = $_POST['plate_number'];
    $driver_name = $_POST['driver_name'];

    $stmt = $pdo->prepare("INSERT INTO vehicles (order_id, vehicle_type, plate_number, driver_name) VALUES (?, ?, ?, ?)");
    $stmt->execute([$order_id, $vehicle_type, $plate_number, $driver_name]);
}

$users = $pdo->query("SELECT id, fullname, email, username, country, created_at FROM users ORDER BY created_at DESC")->fetchAll();
$guides = $pdo->query("SELECT * FROM guides ORDER BY created_at DESC")->fetchAll();
$orders = $pdo->query("SELECT o.id, u.fullname AS user_name, g.name AS guide_name, o.booking_date, o.created_at 
                      FROM orders o 
                      JOIN users u ON o.user_id = u.id 
                      JOIN guides g ON o.guide_id = g.id 
                      ORDER BY o.created_at DESC")->fetchAll();
$vehicles = $pdo->query("SELECT id, vehicle_type, plate_number FROM vehicles ORDER BY vehicle_type ASC")->fetchAll();
$vehicles = $pdo->query("SELECT v.*, u.fullname AS user_name, g.name AS guide_name 
                         FROM vehicles v 
                         JOIN orders o ON v.order_id = o.id 
                         JOIN users u ON o.user_id = u.id 
                         JOIN guides g ON o.guide_id = g.id 
                         ORDER BY v.created_at DESC")->fetchAll();

$available_orders = $pdo->query("SELECT o.id, u.fullname AS user_name, g.name AS guide_name 
                                 FROM orders o 
                                 JOIN users u ON o.user_id = u.id 
                                 JOIN guides g ON o.guide_id = g.id")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin Panel - Explore World</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet" />
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
    <a href="#" class="nav-link" data-section="vehicles">
  <i class="fas fa-car"></i> <span class="link-text">Vehicles</span>
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

  <div id="orders" class="section" style="display:none;">
    <div class="card">
      <h2>Orders / Bookings</h2>
      <table>
        <thead>
          <tr><th>ID</th><th>User</th><th>Guide</th><th>Booking Date</th><th>Booked On</th></tr>
        </thead>
        <tbody>
          <?php foreach ($orders as $order): ?>
          <tr>
            <td><?= htmlspecialchars($order['id']) ?></td>
            <td><?= htmlspecialchars($order['user_name']) ?></td>
            <td><?= htmlspecialchars($order['guide_name']) ?></td>
            <td><?= htmlspecialchars($order['booking_date']) ?></td>
            <td><?= htmlspecialchars($order['created_at']) ?></td>
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
