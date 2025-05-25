<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Please log in to book a table.'); window.location.href='login.php';</script>";
    exit();
}

$name = htmlspecialchars($_POST['name']);
$phone = htmlspecialchars($_POST['phone']);
$email = htmlspecialchars($_POST['email']);
$number_of_people = intval($_POST['number_of_people']);
$booking_date = $_POST['booking_date'];
$booking_time = $_POST['booking_time'];
$duration = intval($_POST['duration']);
$special_request = htmlspecialchars($_POST['special_request']);
$user_id = $_SESSION['user_id'];

$booking_start = new DateTime("$booking_date $booking_time");
$booking_end = clone $booking_start;
$booking_end->modify("+$duration hours");

$start_time_str = $booking_start->format('H:i:s');
$end_time_str = $booking_end->format('H:i:s');

// 1. Get all table numbers
$tables = [];
$tables_result = $conn->query("SELECT table_number FROM tables ORDER BY table_number ASC");
while ($row = $tables_result->fetch_assoc()) {
    $tables[] = $row['table_number'];
}

// 2. Get booked tables for the date that overlap requested time
$sql = "
    SELECT table_number FROM table_bookings
    WHERE booking_date = ?
    AND (
        (booking_time < ? AND ADDTIME(booking_time, SEC_TO_TIME(duration*3600)) > ?)
    )
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $booking_date, $end_time_str, $start_time_str);
$stmt->execute();
$result = $stmt->get_result();

$booked_tables = [];
while ($row = $result->fetch_assoc()) {
    $booked_tables[] = $row['table_number'];
}

$stmt->close();

// 3. Find available tables
$available_tables = array_diff($tables, $booked_tables);

if (empty($available_tables)) {
    echo "<script>alert('Sorry, no tables are available for the selected time. Please choose another time.'); window.history.back();</script>";
    exit();
}

// 4. Assign first available table
$table_number = array_shift($available_tables);

// 5. Insert booking with assigned table number
$insert = $conn->prepare("INSERT INTO table_bookings (user_id, name, phone, email, number_of_people, booking_date, booking_time, duration, special_request, table_number) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$insert->bind_param("isssissisi", $user_id, $name, $phone, $email, $number_of_people, $booking_date, $booking_time, $duration, $special_request, $table_number);

if ($insert->execute()) {
    echo "<script>alert('Table {$table_number} booked successfully!'); window.location.href='index.php';</script>";
} else {
    echo "Error: " . $insert->error;
}

$insert->close();
$conn->close();





