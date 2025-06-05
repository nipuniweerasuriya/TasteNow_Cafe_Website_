<?php
global $conn;
require_once '../Backend/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_item_id'])) {
    $order_item_id = intval($_POST['order_item_id']);

    // Step 1: Fetch current status and price of the item
    $stmt = $conn->prepare("SELECT status, price FROM processed_order_items WHERE id = ?");
    $stmt->bind_param('i', $order_item_id);
    $stmt->execute();
    $stmt->bind_result($current_status, $item_price);
    $stmt->fetch();
    $stmt->close();

    if ($current_status === 'Pending' || $current_status === 'Preparing') {
        // Step 2: Update status to 'Canceled'
        $update = $conn->prepare("UPDATE processed_order_items SET status = 'Canceled' WHERE id = ?");
        $update->bind_param('i', $order_item_id);

        if ($update->execute()) {
            if ($current_status === 'Preparing') {
                $penalty = number_format($item_price * 0.1, 2); // 10% penalty

                // Store penalty in the database
                $penaltyUpdate = $conn->prepare("UPDATE processed_order_items SET cancellation_penalty = ? WHERE id = ?");
                $penaltyUpdate->bind_param('di', $penalty, $order_item_id);
                $penaltyUpdate->execute();
                $penaltyUpdate->close();

                echo "<script>
                alert('Order canceled. You should pay 10% of the item: LKR {$penalty}');
                window.location.href = '../Backend/profile.php';
              </script>";
            } else {
                header("Location: ../Backend/profile.php");
                exit();
            }
        }
        else {
            echo "Error canceling item: " . $update->error;
        }
        $update->close();
    } else {
        echo "<script>
                alert('This item cannot be canceled.');
                window.location.href = '../Backend/profile.php';
              </script>";
    }
} else {
    echo "Invalid request.";
}
