<?php
// Get Daily Summary for the Admin DashBoard
header('Content-Type: application/json');
include '../Backend/db_connect.php';

$summary = [];

// Total Orders Today
$result = $conn->query("SELECT COUNT(*) AS total_orders FROM processed_order WHERE DATE(order_date) = CURDATE()");
$row = $result->fetch_assoc();
$summary['total_orders'] = (int)$row['total_orders'];

// Total Revenue Today
$result = $conn->query("
    SELECT IFNULL(SUM(poi.total_price), 0) AS total_revenue
    FROM processed_order_items poi
    JOIN processed_order po ON poi.order_id = po.id
    WHERE poi.status = 'Paid' AND DATE(po.order_date) = CURDATE()
");
$row = $result->fetch_assoc();
$summary['total_revenue'] = (float)$row['total_revenue'];

// Total Bookings Today (only active)
$result = $conn->query("
    SELECT COUNT(*) AS total_bookings 
    FROM table_bookings 
    WHERE booking_date = CURDATE() AND status = 'active'
");
$row = $result->fetch_assoc();
$summary['total_bookings'] = (int)$row['total_bookings'];

// ✅ Save to daily_summary_logs if not already saved
$summaryDate = date('Y-m-d');

$check = $conn->prepare("SELECT id FROM daily_summary_logs WHERE summary_date = ?");
$check->bind_param("s", $summaryDate);
$check->execute();
$check->store_result();

if ($check->num_rows === 0) {
    $insert = $conn->prepare("
        INSERT INTO daily_summary_logs 
        (summary_date, total_orders, total_revenue, total_bookings) 
        VALUES (?, ?, ?, ?)
    ");
    $insert->bind_param(
        "sidi",
        $summaryDate,
        $summary['total_orders'],
        $summary['total_revenue'],
        $summary['total_bookings']
    );
    $insert->execute();
    $insert->close();
}

$check->close();
$conn->close();

echo json_encode($summary);

