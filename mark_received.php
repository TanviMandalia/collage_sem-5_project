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

// Verify the order belongs to the user and is in a shippable state
$order_check = mysqli_query($conn, 
    "SELECT status FROM `order` 
     WHERE order_id = $order_id AND user_id = $user_id AND status = 'Shipped' AND received = 0");

if (mysqli_num_rows($order_check) === 0) {
    redirectWithMessage('order_history.php', 'error', 'Order cannot be marked as received or does not exist');
}

// Mark order as received
$update = mysqli_query($conn, 
    "UPDATE `order` 
     SET received = 1, status = 'Delivered'
     WHERE order_id = $order_id AND user_id = $user_id");

if ($update) {
    redirectWithMessage('order_history.php', 'success', 'Thank you for confirming receipt of your order!');
} else {
    redirectWithMessage('order_history.php', 'error', 'Failed to update order status. Please try again.');
}
?>
