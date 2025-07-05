<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: login.html");
  exit();
}

$user_id = $_SESSION['user_id'];

// Fetch latest reference number
$booking = $pdo->prepare("SELECT reference_number FROM guide_hotel_bookings WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
$booking->execute([$user_id]);
$ref_number = $booking->fetchColumn();

// Fetch cart items
$cart = $pdo->prepare("
  SELECT c.id AS cart_id, c.quantity, p.name, p.price, p.image
  FROM cart c
  JOIN products p ON c.product_id = p.id
  WHERE c.user_id = ?
");
$cart->execute([$user_id]);
$items = $cart->fetchAll();

// Handle remove
if (isset($_POST['remove'])) {
  $pdo->prepare("DELETE FROM cart WHERE id = ?")->execute([$_POST['cart_id']]);
  header("Location: cart.php");
  exit();
}

// Handle place order
if (isset($_POST['place_order'])) {
  foreach ($items as $item) {
    $pdo->prepare("INSERT INTO orders (user_id, product_name, quantity, price, reference_number) VALUES (?, ?, ?, ?, ?)")
        ->execute([$user_id, $item['name'], $item['quantity'], $item['price'], $ref_number]);
  }
  $pdo->prepare("DELETE FROM cart WHERE user_id = ?")->execute([$user_id]);
  header("Location: cart.php?success=1");
  exit();
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Your Cart</title>
  <style>
    body { font-family: Poppins, sans-serif; background: #f8f8f8; padding: 30px; }
    table { width: 100%; border-collapse: collapse; background: white; }
    th, td { padding: 12px; border-bottom: 1px solid #ccc; text-align: center; }
    h2 { margin-bottom: 20px; }
    img { width: 60px; }
    .btn {
      background: #1e88e5;
      color: white;
      border: none;
      padding: 8px 14px;
      border-radius: 6px;
      cursor: pointer;
      font-weight: 600;
    }
    .btn:hover { background: #1565c0; }
    .success {
      background: #d4edda;
      color: #155724;
      padding: 10px;
      border-radius: 8px;
      margin-bottom: 15px;
    }
  </style>
</head>
<body>

<h2>🛒 My Cart - Ref: <?= htmlspecialchars($ref_number ?? 'N/A') ?></h2>

<?php if (isset($_GET['success'])): ?>
  <div class="success">✅ Order Placed Successfully! Your items will be delivered to your hotel.</div>
<?php endif; ?>

<?php if (count($items) > 0): ?>
  <form method="POST">
    <table>
      <tr><th>Image</th><th>Name</th><th>Qty</th><th>Price</th><th>Total</th><th>Action</th></tr>
      <?php
        $grand_total = 0;
        foreach ($items as $item):
          $total = $item['quantity'] * $item['price'];
          $grand_total += $total;
      ?>
        <tr>
          <td><img src="<?= htmlspecialchars($item['image']) ?>"></td>
          <td><?= htmlspecialchars($item['name']) ?></td>
          <td><?= $item['quantity'] ?></td>
          <td>Rs <?= number_format($item['price'], 2) ?></td>
          <td>Rs <?= number_format($total, 2) ?></td>
          <td>
            <form method="POST">
              <input type="hidden" name="cart_id" value="<?= $item['cart_id'] ?>">
              <button name="remove" class="btn">Remove</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </table>

    <h3>Total: Rs <?= number_format($grand_total, 2) ?></h3>
    <button name="place_order" class="btn">✅ Place Order</button>
  </form>
<?php else: ?>
  <p>Your cart is empty.</p>
<?php endif; ?>
<?php if (isset($_GET['success'])): ?>
  <div class="success">✅ Order Placed Successfully! Your items will be delivered to your hotel.</div>
  <a href="generate_invoice.php" class="btn" style="margin-top: 10px;">📄 Download Invoice</a>
<?php endif; ?>

</body>
</html>
