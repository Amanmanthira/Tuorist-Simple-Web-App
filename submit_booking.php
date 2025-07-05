<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

// DB connection
$conn = new mysqli("localhost", "root", "", "project");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to generate unique reference number
function generateReferenceNumber($conn) {
    $prefix = "BK" . date("Y");  // Example: BK2025
    $sql = "SELECT reference_number FROM guide_hotel_bookings WHERE reference_number LIKE ? ORDER BY id DESC LIMIT 1";
    $stmt = $conn->prepare($sql);
    $likeParam = $prefix . '%';
    $stmt->bind_param("s", $likeParam);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        // Get last 4 digits and increment
        $lastRef = $row['reference_number'];
        $number = (int)substr($lastRef, -4);
        $number++;
    } else {
        $number = 1;
    }
    $stmt->close();

    // Pad number to 4 digits, e.g. 0001
    $numberPadded = str_pad($number, 4, '0', STR_PAD_LEFT);

    return $prefix . $numberPadded;  // e.g. BK20250001
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $guide_id = $_POST['guide_id'] ?? null;
    $hotel_id = $_POST['hotel_id'] ?? null;
    $room_type = $_POST['room_type'] ?? null;
    $tour_type = $_POST['tour_type'] ?? null;
    $people_count = $_POST['people_count'] ?? null;
    $start_date = $_POST['start_date'] ?? null;
    $end_date = $_POST['end_date'] ?? null;

    // Basic validation
    if (!$guide_id || !$hotel_id || !$room_type || !$tour_type || !$people_count || !$start_date || !$end_date) {
        die("Please fill in all required fields.");
    }

    // Calculate days (difference between end_date and start_date)
    $date1 = new DateTime($start_date);
    $date2 = new DateTime($end_date);
    $interval = $date1->diff($date2);
    $days = $interval->days;
    if ($days < 1) $days = 1; // Minimum 1 day

    // Generate reference number
    $reference_number = generateReferenceNumber($conn);

    // Insert booking
 $stmt = $conn->prepare("INSERT INTO guide_hotel_bookings 
  (user_id, guide_id, hotel_id, booking_date, tour_type, room_type, people_count, start_date, end_date, days, created_at, reference_number) 
  VALUES (?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?, NOW(), ?)");

$stmt->bind_param("iiississis", 
    $user_id, 
    $guide_id, 
    $hotel_id, 
    $tour_type, 
    $room_type, 
    $people_count, 
    $start_date, 
    $end_date, 
    $days, 
    $reference_number
);


    if ($stmt->execute()) {
        echo "Booking successful! Your reference number is: <strong>$reference_number</strong>";
        // You can redirect or show a success page here
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>
