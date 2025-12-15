<?php
require_once __DIR__ . '/process_order.php';

// Check if user is logged in
if (!isset($_SESSION['r_id'])) {
    redirectWithMessage('login.php', 'error', 'Please login to manage your orders');
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirectWithMessage('order_history.php', 'error', 'Invalid order ID');
}

$order_id = (int)$_GET['id'];
$user_id = (int)$_SESSION['r_id'];

// Verify the order belongs to the user and is in a cancellable state
$order_check = mysqli_query($conn, 
    "SELECT status FROM `order` 
     WHERE order_id = $order_id AND user_id = $user_id AND status = 'Pending'");

if (mysqli_num_rows($order_check) === 0) {
    redirectWithMessage('order_history.php', 'error', 'Order cannot be cancelled or does not exist');
}

// Update order status to Cancelled
$update = mysqli_query($conn, 
    "UPDATE `order` 
     SET status = 'Cancelled' 
     WHERE order_id = $order_id AND user_id = $user_id");

if ($update) {
    // Restore product stock
    $order_items = mysqli_query($conn, 
        "SELECT product_id, quantity FROM order_items 
         WHERE order_id = $order_id");
    
    while ($item = mysqli_fetch_assoc($order_items)) {
        mysqli_query($conn,
            "UPDATE products 
             SET stock = stock + {$item['quantity']} 
             WHERE product_id = {$item['product_id']}");
    }
    
    redirectWithMessage('order_history.php', 'success', 'Order has been cancelled successfully');
} else {
    redirectWithMessage('order_history.php', 'error', 'Failed to cancel order. Please try again.');
}
?>
