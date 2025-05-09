<?php
include 'db_connect.php';


$sql = "SELECT id, name, price, image_url FROM menu_items WHERE available = 1";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($item = $result->fetch_assoc()) {
        echo '<div class="menu-item">';
        echo '<img src="../Backend/' . htmlspecialchars($item['image_url']) . '" alt="' . htmlspecialchars($item['name']) . '" class="menu-image"/>';
        echo '<h6>' . htmlspecialchars($item['name']) . '</h6>';
        echo '<p>Rs.' . number_format($item['price'], 2) . '</p>';
        echo '<button class="add-to-cart-btn">Add to Cart</button>';
        echo '</div>';
    }
} else {
    echo '<p>No menu items available.</p>';
}
$conn->close();
?>
