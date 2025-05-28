<?php
session_start();
require_once '../Backend/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];
$booking_id = $_POST['booking_id'] ?? null;

if (!$booking_id) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Booking ID is required']);
    exit();
}

// Verify booking belongs to user
$sql = "SELECT user_id FROM table_bookings WHERE booking_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $booking_id);
$stmt->execute();
$stmt->bind_result($owner_id);
$stmt->fetch();
$stmt->close();

if (!$owner_id || $owner_id != $user_id) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Not allowed to cancel this booking']);
    exit();
}

// Update booking status to 'cancelled'
$update_sql = "UPDATE table_bookings SET status = 'cancelled' WHERE booking_id = ?";
$update_stmt = $conn->prepare($update_sql);
$update_stmt->bind_param('i', $booking_id);

if ($update_stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Booking cancelled']);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to cancel booking']);
}

$update_stmt->close();
$conn->close();
