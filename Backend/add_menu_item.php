<?php
// Add Menu Items to The db Menu Items Table
global $conn;
include '../Backend/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['item_name'];
    $price = $_POST['item_price'];
    $category_id = $_POST['category_id'];

    // Validate category
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

        // ✅ Create unique file name
        $imageName = time() . '_' . basename($_FILES['item_image']['name']);
        $imagePath = $imageName;  // Store only the file name in the DB

        // ✅ Define and create upload folder if not exists
        $uploadDir = '../Backend/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // ✅ Move file to the uploads folder
        move_uploaded_file($imageTmp, $uploadDir . $imagePath);
    }


    // Insert into menu_items
    $stmt = $conn->prepare("INSERT INTO menu_items (name, price, image_url, category_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sdsi", $name, $price, $imagePath, $category_id);
    $stmt->execute();
    $stmt->close();

    echo "Menu item added successfully!";
}

$conn->close();


