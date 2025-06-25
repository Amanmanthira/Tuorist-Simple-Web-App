<?php
session_start();
header('Content-Type: application/json');
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'You must be logged in to book a guide.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $guide_id = $_POST['guide_id'] ?? null;
    $booking_date = $_POST['booking_date'] ?? null;

    if (!$guide_id || !$booking_date) {
        echo json_encode(['error' => 'Please select a guide and booking date.']);
        exit;
    }

    // Validate date format and future date
    $today = date('Y-m-d');
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $booking_date) || $booking_date < $today) {
        echo json_encode(['error' => 'Please select a valid booking date in the future.']);
        exit;
    }

    // Check if guide exists
    $stmt = $pdo->prepare("SELECT id FROM guides WHERE id = ?");
    $stmt->execute([$guide_id]);
    if (!$stmt->fetch()) {
        echo json_encode(['error' => 'Selected guide does not exist.']);
        exit;
    }

    // Insert order/booking
    $stmt = $pdo->prepare("INSERT INTO orders (user_id, guide_id, booking_date) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $guide_id, $booking_date]);

    echo json_encode(['success' => true]);
    exit;
}

echo json_encode(['error' => 'Invalid request method']);
