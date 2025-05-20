<?php
global $conn;
session_start();
include 'db_connect.php';

function redirectByRole($role) {
    switch ($role) {
        case 'admin':
            header("Location: ../Backend/admin_dashboard.php");
            break;
        case 'kitchen':
            header("Location: ../Backend/kitchen.php");
            break;
        case 'cashier':
            header("Location: ../Backend/cashier.php");
            break;
        case 'user':
            header("Location: ../Backend/profile.php");
            break;
        default:
            echo "Unknown role.";
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Fetch user by email + role
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND role = ? AND status = 1 LIMIT 1");
    $stmt->bind_param("ss", $email, $role);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        if (password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['role'] = $user['role'];
            redirectByRole($user['role']);
        } else {
            echo "Incorrect password.";
        }
    } else {
        echo "Invalid email or role.";
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Invalid request.";
}
