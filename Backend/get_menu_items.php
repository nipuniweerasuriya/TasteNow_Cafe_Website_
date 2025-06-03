<?php
// Display Menu Items In the Home Page
include 'db_connect.php';


$sql = "SELECT mi.id, mi.name, mi.price, mi.image_url, c.name AS category_name 
        FROM menu_items mi
        JOIN categories c ON mi.category_id = c.id";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($item = $result->fetch_assoc()) {
        $itemId = $item['id'];
        $itemName = htmlspecialchars($item['name']);
        $itemPrice = number_format($item['price'], 2);
        $itemImage = !empty($item['image_url'])
            ? '../Backend/uploads/' . htmlspecialchars($item['image_url'])
            : '../Backend/uploads/default.jpg';
        $categoryName = htmlspecialchars($item['category_name']);

        echo '<div class="menu-item visible" data-category="' . $categoryName . '">';
        echo '  <img src="' . $itemImage . '" alt="' . $itemName . '" class="menu-image">';
        echo '  <h6 class="menu-name">' . $itemName . '</h6>';
        echo '  <p class="menu-price">Rs. ' . $itemPrice . '</p>';
        echo '  <button class="add-to-cart-btn" 
                      data-id="' . $itemId . '" 
                      data-name="' . $itemName . '" 
                      data-price="' . $item['price'] . '" 
                      data-image="' . $itemImage . '">
                      Add To Cart
                </button>';
        echo '</div>';
    }
} else {
    echo '<p>No menu items available.</p>';
}

$conn->close();
