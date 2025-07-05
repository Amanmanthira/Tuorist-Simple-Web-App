<?php
session_start();
require 'db.php';
require 'vendor/autoload.php'; // Dompdf autoload

use Dompdf\Dompdf;

if (!isset($_SESSION['user_id'])) {
  die("Unauthorized.");
}

$user_id = $_SESSION['user_id'];

// Fetch user info
$user_stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
$user_stmt->execute([$user_id]);
$user = $user_stmt->fetch();

$booking_stmt = $pdo->prepare("
  SELECT b.reference_number, h.name AS hotel_name, h.location
  FROM guide_hotel_bookings b
  JOIN hotels h ON b.hotel_id = h.id
  WHERE b.user_id = ?
  ORDER BY b.created_at DESC LIMIT 1
");
$booking_stmt->execute([$user_id]);
$booking = $booking_stmt->fetch();

// Get latest order items
$orders_stmt = $pdo->prepare("
  SELECT * FROM orders 
  WHERE user_id = ? 
  ORDER BY created_at DESC LIMIT 10
");
$orders_stmt->execute([$user_id]);
$orders = $orders_stmt->fetchAll();

$total = 0;
$html = '
  <h2 style="text-align:center;">🧾 Ceylon Market - Order Invoice</h2>
  <hr>
  <p><strong>Customer:</strong> ' . htmlspecialchars($user['username']) . '</p>
  <p><strong>Reference Number:</strong> ' . htmlspecialchars($booking['reference_number']) . '</p>
  <p><strong>Hotel:</strong> ' . htmlspecialchars($booking['hotel_name']) . ' - ' . htmlspecialchars($booking['location']) . '</p>
  <p><strong>Date:</strong> ' . date("F j, Y, g:i A") . '</p>
  <br>
  <table width="100%" border="1" cellspacing="0" cellpadding="8">
    <tr>
      <th>Product</th>
      <th>Qty</th>
      <th>Price</th>
      <th>Total</th>
    </tr>';

foreach ($orders as $item) {
  $item_total = $item['price'] * $item['quantity'];
  $total += $item_total;
  $html .= '
    <tr>
      <td>' . htmlspecialchars($item['product_name']) . '</td>
      <td>' . $item['quantity'] . '</td>
      <td>Rs ' . number_format($item['price'], 2) . '</td>
      <td>Rs ' . number_format($item_total, 2) . '</td>
    </tr>';
}

$html .= '
  </table>
  <h3 style="text-align:right;">Total: Rs ' . number_format($total, 2) . '</h3>
  <p style="text-align:center;">Thank you for shopping with us 🛍️</p>';

// Generate PDF
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("Invoice_Ceylon_Market.pdf", ["Attachment" => true]); // Download directly
exit;
?>
