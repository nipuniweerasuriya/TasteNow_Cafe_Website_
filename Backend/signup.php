<?php
global $conn;
session_start();
include 'db_connect.php'; // your DB connection file

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm  = $_POST['confirm-password'];
    $role     = $_POST['role'];

    // Basic validation
    if ($password !== $confirm) {
        die("Passwords do not match.");
    }

    // Enforce role uniqueness for admin, kitchen, cashier
    if (in_array($role, ['admin', 'kitchen', 'cashier'])) {
        $checkSql = "SELECT id FROM users WHERE role = ? LIMIT 1";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("s", $role);
        $checkStmt->execute();
        $checkStmt->store_result();

        if ($checkStmt->num_rows > 0) {
            die("A user with the '$role' role already exists. Please choose a different role.");
        }

        $checkStmt->close();
    }

    // Hash the password
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // Insert the user
    $sql = "INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $name, $email, $passwordHash, $role);

    try {
        if ($stmt->execute()) {
            // Redirect to index.php after successful registration
            header("Location: ../Frontend/index.php");
            exit;
        } else {
            echo "Error: Could not register user.";
        }
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1062) {
            echo "Email already exists.";
        } else {
            echo "Database error: " . $e->getMessage();
        }
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Invalid request.";
}
?>

