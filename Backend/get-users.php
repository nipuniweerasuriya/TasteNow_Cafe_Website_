<?php
// get-users.php
include '../Backend/db_connect.php'; // Adjust this path if needed


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Make sure your `users` table has a 'role' column
$sql = "SELECT id, name, email, role FROM users";
$result = $conn->query($sql);

$users = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}

echo json_encode($users);

$conn->close();
?>


