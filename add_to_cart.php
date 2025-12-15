<?php
if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}
require_once __DIR__ . '/include/connection.php';

// Set default redirect URL
$redirect = isset($_GET['redirect']) ? filter_var($_GET['redirect'], FILTER_SANITIZE_STRING) : 'products.php';

// Validate product ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "Invalid product ID.";
    header('Location: ' . $redirect);
    exit;
}

// Sanitize input
$product_id = (int)$_GET['id'];
$qty = isset($_GET['qty']) && is_numeric($_GET['qty']) && (int)$_GET['qty'] > 0 ? (int)$_GET['qty'] : 1;
$user_id = isset($_SESSION['r_id']) ? (int)$_SESSION['r_id'] : 0;

// Fetch product details using prepared statement
$product = [];
$sql = "SELECT * FROM products WHERE product_id = ? OR id = ? LIMIT 1";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    error_log("Failed to prepare statement: " . $conn->error);
    $_SESSION['error'] = "Database error. Please try again.";
    header('Location: ' . $redirect);
    exit;
}

$stmt->bind_param("ii", $product_id, $product_id);

if (!$stmt->execute()) {
    error_log("Query execution failed: " . $stmt->error);
    $_SESSION['error'] = "Failed to fetch product details.";
    header('Location: ' . $redirect);
    exit;
}

$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $product = $result->fetch_assoc();
} else {
    $_SESSION['error'] = "Product not found!";
    header('Location: ' . $redirect);
    exit;
}

// For logged-in users
if ($user_id > 0) {{
    try {
        // First check if the cart table exists and has the right structure
        $check_table = $conn->query("SHOW TABLES LIKE 'cart'");
        if ($check_table->num_rows === 0) {
            throw new Exception("Cart table does not exist");
        }

        // Check if product already exists in cart
        $stmt = $conn->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
        if ($stmt === false) {
            throw new Exception("Failed to prepare cart check statement: " . $conn->error);
        }
        
        $stmt->bind_param("ii", $user_id, $product_id);
        if (!$stmt->execute()) {
            throw new Exception("Failed to execute cart check: " . $stmt->error);
        }
        
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Update quantity if product exists
            $cart_item = $result->fetch_assoc();
            $new_qty = ($cart_item['quantity'] ?? $cart_item['qty'] ?? 0) + $qty;
            
            $update = $conn->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ?");
            if ($update === false) {
                throw new Exception("Failed to prepare update statement: " . $conn->error);
            }
            
            $cart_id = $cart_item['cart_id'] ?? $cart_item['id'] ?? 0;
            $update->bind_param("ii", $new_qty, $cart_id);
            
            if (!$update->execute()) {
                throw new Exception("Failed to update cart: " . $update->error);
            }
            
            $_SESSION['success'] = "Cart updated successfully!";
        } else {
            // Add new item to cart
            $product_name = $product['name'] ?? $product['product_nm'] ?? 'Unknown Product';
            $product_price = (float)($product['price'] ?? $product['product_price'] ?? 0);
            $product_image = $product['image'] ?? $product['product_image'] ?? '';
            
            $insert = $conn->prepare("INSERT INTO cart (user_id, product_id, product_name, product_price, product_image, quantity) 
                                    VALUES (?, ?, ?, ?, ?, ?)");
            if ($insert === false) {
                throw new Exception("Failed to prepare insert statement: " . $conn->error);
            }
            
            $insert->bind_param("iisdsi", $user_id, $product_id, $product_name, $product_price, $product_image, $qty);
            
            if (!$insert->execute()) {
                throw new Exception("Failed to add to cart: " . $insert->error);
            }
            
            $_SESSION['success'] = "Product added to cart successfully!";
        }
    } catch (Exception $e) {
        error_log("Cart Error: " . $e->getMessage());
        $_SESSION['error'] = "An error occurred while updating your cart. Please try again.";
    }
    }
} else {
    // Guest: Use session cart
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Check if product already exists in cart
    if (isset($_SESSION['cart'][$product_id])) {
        // Update quantity if product exists
        $_SESSION['cart'][$product_id]['qty'] += $qty;
    } else {
        // Add new item to cart
        $_SESSION['cart'][$product_id] = [
            'id' => $product_id,
            'name' => $product['name'] ?? $product['product_nm'] ?? 'Unknown Product',
            'price' => (float)($product['price'] ?? $product['product_price'] ?? 0),
            'image' => $product['image'] ?? $product['product_image'] ?? '',
            'qty' => $qty
        ];
    }
    
    $_SESSION['success'] = "Product added to cart!";
}

// Redirect back to the previous page with success message
$sep = (strpos($redirect, '?') === false) ? '?' : '&';
header('Location: ' . $redirect . $sep . 'added=1');
exit;
