<?php
// Include your database connection
include('db_connect.php');

// Query to fetch table bookings
$query = "SELECT booking_id, customer_name, table_number, booking_time, status FROM table_bookings";
$result = mysqli_query($conn, $query);

// Check if the query ran successfully
if (!$result) {
    echo json_encode(['error' => 'Failed to fetch table bookings']);
    exit;
}

$tableBookings = [];

// Fetch all the results and store them in an array
while ($row = mysqli_fetch_assoc($result)) {
    $tableBookings[] = $row;
}

// Return the results as a JSON response
echo json_encode($tableBookings);

// Close the database connection
mysqli_close($conn);




