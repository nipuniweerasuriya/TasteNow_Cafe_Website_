<?php
global $conn;
require 'db_connect.php'; // DB connection

$imagePath = '';
$item_id = 0;

// Handle image upload
if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    $filename = time() . '_' . basename($_FILES['image_file']['name']);
    $targetPath = $uploadDir . $filename;

    if (move_uploaded_file($_FILES['image_file']['tmp_name'], $targetPath)) {
        $imagePath = $targetPath;
    }
}

// Get form data
$name = $_POST['name'];
$description = $_POST['description'] ?? '';
$price = $_POST['price'];
$category_id = $_POST['category_id'];

// Insert main menu item
$stmt = $conn->prepare("INSERT INTO menu_items (name, description, price, image_url, category_id) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("ssdsi", $name, $description, $price, $imagePath, $category_id);
$stmt->execute();
$item_id = $stmt->insert_id;
$stmt->close();

// ✅ Reusable function to insert multiple rows
function insertExtras($conn, $table, $item_id, $names, $prices, $colName, $priceCol) {
    $query = "INSERT INTO $table (item_id, $colName, $priceCol) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    for ($i = 0; $i < count($names); $i++) {
        $n = $names[$i];
        $p = $prices[$i];
        if ($n !== '' && is_numeric($p)) {
            $stmt->bind_param("isd", $item_id, $n, $p);
            $stmt->execute();
        }
    }
    $stmt->close();
}

// Insert variants
if (!empty($_POST['variants']) && !empty($_POST['variant_prices'])) {
    insertExtras($conn, 'menu_variants', $item_id, $_POST['variants'], $_POST['variant_prices'], 'variant_name', 'price');
}

// Insert add-ons
if (!empty($_POST['addons']) && !empty($_POST['addon_prices'])) {
    insertExtras($conn, 'menu_add_ons', $item_id, $_POST['addons'], $_POST['addon_prices'], 'addon_name', 'addon_price');
}

echo "Menu item added successfully!";
?>
