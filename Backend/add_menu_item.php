<?php
global $conn;
include '../Backend/db_connect.php'; // Make sure the path is correct

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get basic item data
    $name = $_POST['item_name'];
    $price = $_POST['item_price'];
    $category_id = $_POST['category_id'];

    // Validate category_id
    $category_check = $conn->prepare("SELECT id FROM categories WHERE id = ?");
    $category_check->bind_param("i", $category_id);
    $category_check->execute();
    $category_check_result = $category_check->get_result();

    if ($category_check_result->num_rows == 0) {
        echo "Invalid category_id. Please select a valid category.";
        exit();
    }

    // Handle image upload
    $imagePath = null;
    if (isset($_FILES['item_image']) && $_FILES['item_image']['error'] === UPLOAD_ERR_OK) {
        $imageTmp = $_FILES['item_image']['tmp_name'];
        $imageName = basename($_FILES['item_image']['name']);
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $imagePath = $uploadDir . time() . '_' . $imageName;
        move_uploaded_file($imageTmp, $imagePath);
    }

    // Insert main menu item
    $stmt = $conn->prepare("INSERT INTO menu_items (name, price, image_url, category_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sdsi", $name, $price, $imagePath, $category_id);
    $stmt->execute();
    $item_id = $stmt->insert_id;
    $stmt->close();

    // Insert variants
    if (!empty($_POST['variants']) && is_array($_POST['variants'])) {
        $variants = $_POST['variants'];
        $variant_prices = $_POST['variant_prices'];
        $stmt = $conn->prepare("INSERT INTO menu_variants (item_id, variant_name, price) VALUES (?, ?, ?)");
        foreach ($variants as $index => $variant_name) {
            $variant_price = $variant_prices[$index] ?? 0;
            if (!empty($variant_name)) {
                $stmt->bind_param("isd", $item_id, $variant_name, $variant_price);
                $stmt->execute();
            }
        }
        $stmt->close();
    }

    // Insert add-ons
    if (!empty($_POST['addons']) && is_array($_POST['addons'])) {
        $addons = $_POST['addons'];
        $addon_prices = $_POST['addon_prices'];
        $stmt = $conn->prepare("INSERT INTO menu_add_ons (item_id, addon_name, addon_price) VALUES (?, ?, ?)");
        foreach ($addons as $index => $addon_name) {
            $addon_price = $addon_prices[$index] ?? 0;
            if (!empty($addon_name)) {
                $stmt->bind_param("isd", $item_id, $addon_name, $addon_price);
                $stmt->execute();
            }
        }
        $stmt->close();
    }

    echo "Menu item added successfully!";
}

$conn->close();



