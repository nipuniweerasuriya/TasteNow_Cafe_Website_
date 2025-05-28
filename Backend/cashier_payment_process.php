<?php

session_start();
include '../Backend/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $order_id = $_POST['order_id'];
    $given_money = floatval($_POST['given_money']);
    $total_amount = floatval($_POST['total_amount']);

    if ($given_money < $total_amount) {
        $_SESSION['error'] = "Insufficient amount";
        header("Location: cashier.php");
        exit();
    }

    $balance = $given_money - $total_amount;

    // Save to payments table
    $stmt = $conn->prepare("INSERT INTO payments (order_id, amount_paid, given_money, balance) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iddd", $order_id, $total_amount, $given_money, $balance);
    $stmt->execute();

    // Update payment status
    $conn->query("UPDATE processed_order SET payment_status = 'Paid' WHERE id = $order_id");

    // Store success in session
    $_SESSION['success'] = "Payment processed successfully! Balance to return: Rs. " . number_format($balance, 2);

    header("Location: cashier.php");
    exit();
}
