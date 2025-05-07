<?php
// Include your database connection file
global $conn;
include 'db_connect.php';

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and get user inputs
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    // Validate the email and password
    if (empty($email) || empty($password)) {
        die("Please fill in both fields.");
    }

    // Prepare SQL to check if the user exists with the given email
    $sql = "SELECT id, name, email, password_hash FROM users WHERE email = ?";

    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }

    $stmt->bind_param("s", $email);

    // Execute the query
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if user exists
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Verify the entered password with the stored hash
        if (password_verify($password, $user['password_hash'])) {
            // Password is correct, start session and log the user in
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];

            // Redirect to the user dashboard or home page
            header("Location: dashboard.php"); // Redirect to your home or dashboard page
            exit();
        } else {
            // Password is incorrect
            echo "Invalid password.";
        }
    } else {
        // No user found with this email
        echo "No user found with this email.";
    }

    $stmt->close();
    $conn->close();
}
?>
