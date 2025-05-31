<?php
include 'db_connect.php'; // Make sure this file contains your DB connection setup

if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $booking_id = intval($_GET['id']);

    // Determine the new status based on the action
    if ($action === 'confirm') {
        $new_status = 'Confirmed';
    } elseif ($action === 'cancel') {
        $new_status = 'Canceled';
    } else {
        echo "Invalid action.";
        exit();
    }

    // Prepare statement to get current status
    $checkQuery = "SELECT status FROM table_bookings WHERE booking_id = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $stmt->bind_result($current_status);
    if (!$stmt->fetch()) {
        echo "Booking not found.";
        $stmt->close();
        exit();
    }
    $stmt->close();

    // Prevent status change if already Confirmed or Canceled
    if ($current_status === 'Confirmed' || $current_status === 'Canceled') {
        echo "Booking has already been $current_status. Action not allowed.";
        exit();
    }

    // Update status
    $updateQuery = "UPDATE table_bookings SET status = ? WHERE booking_id = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("si", $new_status, $booking_id);

    if ($updateStmt->execute()) {
        echo "Booking status successfully updated to $new_status.";
    } else {
        echo "Error updating booking status.";
    }

    $updateStmt->close();
    $conn->close();

} else {
    echo "Invalid request.";
}
