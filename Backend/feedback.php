<?php
session_start();
require_once 'db_connect.php';

// Check if user is logged in and user_id is set in session
if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to submit feedback.");
}

$user_id = $_SESSION['user_id'];
$message = isset($_POST['message']) ? trim($_POST['message']) : '';

if (empty($message)) {
    die("Feedback message cannot be empty.");
}

// Prepare and execute insert statement with user_id
$stmt = $conn->prepare("INSERT INTO user_feedback (message, user_id) VALUES (?, ?)");
$stmt->bind_param("si", $message, $user_id);

if ($stmt->execute()) {
    header('Location: index.php?feedback=success');
    exit();
} else {
    echo "Error saving feedback: " . $stmt->error;
}

$stmt->close();
$conn->close();
