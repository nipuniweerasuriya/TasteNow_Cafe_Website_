<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../Frontend/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_id'])) {
    $userId = $_SESSION['user_id'];
    $bookingId = $_POST['booking_id'];

    // Update the booking status instead of deleting it
    $sql = "UPDATE table_bookings SET status = 'Canceled' WHERE booking_id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $bookingId, $userId);

    if ($stmt->execute()) {
        $_SESSION['msg'] = "Booking canceled successfully.";
    } else {
        $_SESSION['msg'] = "Failed to cancel booking.";
    }

    $stmt->close();
} else {
    $_SESSION['msg'] = "Invalid request.";
}

// Redirect back to profile page
header("Location: ../Backend/profile.php?tab=bookings");
exit();
