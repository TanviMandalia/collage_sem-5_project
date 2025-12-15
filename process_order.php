<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}

require_once __DIR__ . '/include/connection.php';

// Function to safely redirect with message
function redirectWithMessage($url, $type, $message) {
    $separator = (strpos($url, '?') === false) ? '?' : '&';
    header('Location: ' . $url . $separator . $type . '=' . urlencode($message));
    exit;
}

// Check if user is logged in
if (!isset($_SESSION['r_id'])) {
    redirectWithMessage('login.php', 'error', 'Please login to place an order');
}

$user_id = (int)$_SESSION['r_id'];
$redirect = 'checkout.php';

// Check connection
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Process order form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_method = mysqli_real_escape_string($conn, $_POST['payment_method'] ?? '');
    $shipping_address = mysqli_real_escape_string($conn, $_POST['shipping_address'] ?? '');
    
    if (empty($payment_method) || empty($shipping_address)) {
        redirectWithMessage($redirect, 'error', 'Please fill in all required fields');
    }
    
    // Start transaction
    mysqli_begin_transaction($conn);
    
    try {
        // 1. Get cart items and calculate total
        $total_amount = 0;
        $cart_items = [];
        
        if (isset($_SESSION['cart'])) {
            // For guest users with session cart
            foreach ($_SESSION['cart'] as $product_id => $item) {
                $total_amount += $item['price'] * $item['qty'];
                $cart_items[] = $item;
            }
        } else {
            // For logged-in users with cart in database
            $cart_query = mysqli_query($conn, 
                "SELECT c.*, p.price 
                 FROM cart c 
                 JOIN products p ON c.product_id = p.product_id 
                 WHERE c.user_id = $user_id");
            
            if ($cart_query === false) {
                throw new Exception("Error fetching cart items: " . mysqli_error($conn));
            }
            
            while ($item = mysqli_fetch_assoc($cart_query)) {
                $total_amount += $item['price'] * $item['quantity'];
                $cart_items[] = $item;
            }
        }
        
        if (empty($cart_items)) {
            throw new Exception("Your cart is empty");
        }
        
        // 2. Insert order
        $insert_order = mysqli_query($conn, 
            "INSERT INTO `order` (
                user_id, 
                total_amount, 
                status, 
                payment_method, 
                shipping_address,
                order_date
            ) VALUES (
                $user_id, 
                $total_amount, 
                'Pending', 
                '$payment_method', 
                '$shipping_address',
                NOW()
            )");
            
        if ($insert_order === false) {
            throw new Exception("Error creating order: " . mysqli_error($conn));
        }
        
        $order_id = mysqli_insert_id($conn);
        
        // 3. Insert order items
        foreach ($cart_items as $item) {
            $product_id = $item['product_id'] ?? $item['id'];
            $quantity = $item['qty'] ?? $item['quantity'];
            $price = $item['price'];
            
            $insert_item = mysqli_query($conn,
                "INSERT INTO order_items (
                    order_id, 
                    product_id, 
                    quantity, 
                    price
                ) VALUES (
                    $order_id, 
                    $product_id, 
                    $quantity, 
                    $price
                )");
                
            if ($insert_item === false) {
                throw new Exception("Error adding order items: " . mysqli_error($conn));
            }
            
            // Update product stock if needed
            $update_stock = mysqli_query($conn,
                "UPDATE products 
                 SET stock = stock - $quantity 
                 WHERE product_id = $product_id AND stock >= $quantity");
                 
            if (mysqli_affected_rows($conn) === 0) {
                throw new Exception("Insufficient stock for product ID: $product_id");
            }
        }
        
        // 4. Clear cart
        if (isset($_SESSION['cart'])) {
            unset($_SESSION['cart']);
        } else {
            $clear_cart = mysqli_query($conn, "DELETE FROM cart WHERE user_id = $user_id");
            if ($clear_cart === false) {
                throw new Exception("Error clearing cart: " . mysqli_error($conn));
            }
        }
        
        // Commit transaction
        mysqli_commit($conn);
        
        // Redirect to order confirmation
        redirectWithMessage('order_confirmation.php', 'success', 'Order placed successfully! Your order ID is: ' . $order_id);
        
    } catch (Exception $e) {
        // Rollback transaction on error
        mysqli_rollback($conn);
        error_log("Order Error: " . $e->getMessage());
        redirectWithMessage($redirect, 'error', 'Failed to place order: ' . $e->getMessage());
    }
}

// Function to get user's orders
function getUserOrders($conn, $user_id) {
    $orders = [];
    $query = "SELECT * FROM `order` WHERE user_id = $user_id ORDER BY order_date DESC";
    $result = mysqli_query($conn, $query);
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $orders[] = $row;
        }
    }
    
    return $orders;
}

// Function to get order details
function getOrderDetails($conn, $order_id, $user_id = null) {
    $query = "SELECT o.*, oi.*, p.name as product_name, p.image as product_image 
              FROM `order` o 
              JOIN order_items oi ON o.order_id = oi.order_id 
              JOIN products p ON oi.product_id = p.product_id 
              WHERE o.order_id = $order_id";
              
    if ($user_id !== null) {
        $query .= " AND o.user_id = $user_id";
    }
    
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        return false;
    }
    
    $order = null;
    $items = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        if ($order === null) {
            $order = [
                'order_id' => $row['order_id'],
                'user_id' => $row['user_id'],
                'total_amount' => $row['total_amount'],
                'status' => $row['status'],
                'payment_method' => $row['payment_method'],
                'shipping_address' => $row['shipping_address'],
                'order_date' => $row['order_date'],
                'received' => $row['received']
            ];
        }
        
        $items[] = [
            'product_id' => $row['product_id'],
            'product_name' => $row['product_name'],
            'product_image' => $row['product_image'],
            'quantity' => $row['quantity'],
            'price' => $row['price'],
            'subtotal' => $row['quantity'] * $row['price']
        ];
    }
    
    if ($order === null) {
        return false;
    }
    
    $order['items'] = $items;
    return $order;
}
?>
