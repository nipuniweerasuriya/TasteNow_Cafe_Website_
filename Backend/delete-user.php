<?php
include 'db_connect.php'; // Adjust path if needed

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['user_id'])) {
    $user_id = intval($_POST['user_id']);

    $sql = "DELETE FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        echo "User deleted successfully.";
    } else {
        echo "Error deleting user.";
    }

    $stmt->close();
} else {
    echo "User ID not provided.";
}

$conn->close();
