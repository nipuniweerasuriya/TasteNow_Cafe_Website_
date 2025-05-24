<?php
session_start();
include 'db_connect.php'; // your existing DB connection file

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Please log in to book a table.'); window.location.href='login.php';</script>";
    exit();
}

// Sanitize and get POST data
$name = htmlspecialchars($_POST['name']);
$phone = htmlspecialchars($_POST['phone']);
$email = htmlspecialchars($_POST['email']);
$number_of_people = intval($_POST['number_of_people']);
$booking_date = $_POST['booking_date'];
$booking_time = $_POST['booking_time']; // e.g., "18:00:00"
$duration = intval($_POST['duration']); // in hours
$special_request = htmlspecialchars($_POST['special_request']);
$user_id = $_SESSION['user_id'];

// Calculate booking start and end datetime
$booking_start = new DateTime("$booking_date $booking_time");
$booking_end = clone $booking_start;
$booking_end->modify("+$duration hours");

// Convert to string for SQL
$start_str = $booking_start->format('Y-m-d H:i:s');
$end_str = $booking_end->format('Y-m-d H:i:s');

// Check for overlapping bookings on the same date/time
$sql = "
    SELECT * FROM table_bookings
    WHERE booking_date = ? 
    AND (
        (booking_time <= ? AND ADDTIME(booking_time, SEC_TO_TIME(duration*3600)) > ?) OR
        (booking_time < ADDTIME(?, SEC_TO_TIME(?*3600)) AND ADDTIME(booking_time, SEC_TO_TIME(duration*3600)) >= ADDTIME(?, SEC_TO_TIME(?*3600)))
    )
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssss", $booking_date, $booking_time, $booking_time, $booking_time, $duration, $booking_time, $duration);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Overlap exists, booking not possible
    echo "<script>alert('Sorry, the table is not available at the selected time. Please choose another time.'); window.history.back();</script>";
    exit();
} else {
    // No overlap, insert booking
    $insert = $conn->prepare("INSERT INTO table_bookings (user_id, name, phone, email, number_of_people, booking_date, booking_time, duration, special_request) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $insert->bind_param("isssissis", $user_id, $name, $phone, $email, $number_of_people, $booking_date, $booking_time, $duration, $special_request);
    if ($insert->execute()) {
        echo "<script>alert('Table booked successfully!'); window.location.href='index.php';</script>";
    } else {
        echo "Error: " . $insert->error;
    }
    $insert->close();
}

$stmt->close();
$conn->close();




