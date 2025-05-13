<?php
include 'db_connect.php';

// SQL query to fetch the menu items
$sql = "SELECT mi.id, mi.name, mi.price, mi.image_url, c.name AS category_name 
        FROM menu_items mi
        JOIN categories c ON mi.category_id = c.id
        WHERE mi.available = 1";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($item = $result->fetch_assoc()) {
        // Clean the data
        $itemName = htmlspecialchars($item['name']);
        $itemPrice = number_format($item['price'], 2);
        $itemImage = !empty($item['image_url']) ? '../Backend/uploads/' . htmlspecialchars($item['image_url']) : 'default.jpg';
        $categoryName = htmlspecialchars($item['category_name']);
        $itemId = $item['id'];

        // Fetch variants for the current menu item
        $variantSql = "SELECT id, variant_name, price FROM menu_variants WHERE item_id = ?";
        $variantStmt = $conn->prepare($variantSql);
        $variantStmt->bind_param("i", $itemId);
        $variantStmt->execute();
        $variantResult = $variantStmt->get_result();
        $variants = [];
        while ($variant = $variantResult->fetch_assoc()) {
            $variants[] = $variant;
        }

        // Fetch add-ons for the current menu item
        $addonSql = "SELECT id, addon_name, addon_price FROM menu_add_ons WHERE item_id = ?";
        $addonStmt = $conn->prepare($addonSql);
        $addonStmt->bind_param("i", $itemId);
        $addonStmt->execute();
        $addonResult = $addonStmt->get_result();
        $addOns = [];
        while ($addon = $addonResult->fetch_assoc()) {
            $addOns[] = $addon;
        }

        // Encode variants and add-ons to be used in JavaScript
        $variantsJson = json_encode($variants);
        $addOnsJson = json_encode($addOns);

        // Output the menu item HTML along with its variants and add-ons
        echo '<div class="menu-item" data-category="' . $categoryName . '" data-variants=\'' . $variantsJson . '\' data-addons=\'' . $addOnsJson . '\'>';
        echo '  <img src="' . $itemImage . '" alt="' . $itemName . '" class="menu-image"/>';
        echo '  <h6>' . $itemName . '</h6>';
        echo '  <p>Rs. ' . $itemPrice . '</p>';
        echo '  <button class="add-to-cart-btn" data-id="' . $itemId . '">Add To Cart</button>';
        echo '</div>';
    }
} else {
    echo '<p>No menu items available.</p>';
}

$conn->close();
