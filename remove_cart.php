<?php
session_start();
require_once __DIR__ . '/include/connection.php';

// Initialize response array
$response = [
    'success' => false,
    'message' => 'An error occurred',
    'redirect' => 'shoping-cart.php'
];

try {
    // Check if it's a guest cart removal
    if (isset($_GET['sid']) && is_numeric($_GET['sid'])) {
        $pid = (int)$_GET['sid'];
        if (isset($_SESSION['cart'][$pid])) {
            unset($_SESSION['cart'][$pid]);
            $_SESSION['success'] = 'Item removed from cart';
            $response['success'] = true;
        } else {
            $response['message'] = 'Item not found in cart';
        }
    } 
    // Check if it's a logged-in user's cart removal
    elseif (isset($_SESSION['r_id'], $_GET['id']) && is_numeric($_GET['id'])) {
        $user_id = (int)$_SESSION['r_id'];
        $cart_id = (int)$_GET['id'];
        
        // Use prepared statement to prevent SQL injection
        $stmt = $conn->prepare("DELETE FROM cart WHERE cart_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $cart_id, $user_id);
        
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $_SESSION['success'] = 'Item removed from cart';
                $response['success'] = true;
            } else {
                $response['message'] = 'Item not found in your cart';
            }
        } else {
            throw new Exception('Failed to remove item from cart');
        }
        $stmt->close();
    } else {
        $response['message'] = 'Invalid request';
    }
} catch (Exception $e) {
    error_log('Cart Removal Error: ' . $e->getMessage());
    $_SESSION['error'] = 'An error occurred while removing the item';
    $response['message'] = 'An error occurred. Please try again.';
}

// If this is an AJAX request, return JSON
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Regular request, redirect
header('Location: ' . $response['redirect']);
exit;
