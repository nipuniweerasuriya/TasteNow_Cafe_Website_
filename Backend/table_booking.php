<?php
// Start the session
session_start();

require 'db_connect.php';

// Assuming the user is logged in and their user ID is stored in the session
// Check if user is logged in
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id']; // Get user_id from session
} else {
    // Redirect to login page if the user is not logged in
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $number_of_people = $_POST['number_of_people'];
    $booking_date = $_POST['booking_date'];
    $booking_time = $_POST['booking_time'];
    $special_request = $_POST['special_request'];

    // Store data in session (optional, in case you want to show a confirmation before submission)
    $_SESSION['booking'] = [
        'name' => $name,
        'phone' => $phone,
        'email' => $email,
        'number_of_people' => $number_of_people,
        'booking_date' => $booking_date,
        'booking_time' => $booking_time,
        'special_request' => $special_request
    ];

    // Insert data into the table_bookings table
    $sql = "INSERT INTO table_bookings (user_id, name, phone, email, number_of_people, booking_date, booking_time, special_request) 
            VALUES ('$user_id', '$name', '$phone', '$email', '$number_of_people', '$booking_date', '$booking_time', '$special_request')";

    if ($conn->query($sql) === TRUE) {
        // Set success message in session
        $_SESSION['booking_success'] = "Table booked successfully!";
        // Redirect to index.php
    } else {
        // Set error message in session
        $_SESSION['booking_error'] = "Error: " . $sql . "<br>" . $conn->error;
        // Redirect to index.php on error as well
    }
    header("Location:../Frontend/index.php");
    exit();

}

// Close connection
$conn->close();

