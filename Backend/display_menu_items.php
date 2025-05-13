<?php
require_once 'db_connect.php'; // update with your DB connection file

$menuItems = [];

$sql = "SELECT * FROM menu_items WHERE available = 1";
$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    $itemId = $row['id'];

    // Fetch variants
    $variants = [];
    $variantSql = "SELECT variant_name, price FROM menu_variants WHERE item_id = $itemId";
    $variantResult = $conn->query($variantSql);
    while ($v = $variantResult->fetch_assoc()) {
        $variants[] = $v;
    }

    // Fetch add-ons
    $addons = [];
    $addonSql = "SELECT addon_name, addon_price FROM menu_add_ons WHERE item_id = $itemId";
    $addonResult = $conn->query($addonSql);
    while ($a = $addonResult->fetch_assoc()) {
        $addons[] = $a;
    }

    $row['variants'] = $variants;
    $row['addons'] = $addons;

    $menuItems[] = $row;
}

header('Content-Type: application/json');
echo json_encode($menuItems);
