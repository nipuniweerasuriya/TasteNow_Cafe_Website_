<?php
// Display Menu Items In the Admin DashBoard Table
require_once 'db_connect.php';


$sql = "SELECT id, name, price, image_url FROM menu_items";
$result = $conn->query($sql);

$menu_items = [];
while ($row = $result->fetch_assoc()) {
    $menu_items[] = $row;
}

header('Content-Type: application/json');
echo json_encode($menu_items);
$conn->close();


