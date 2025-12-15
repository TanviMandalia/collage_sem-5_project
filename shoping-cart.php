<?php
session_start();
require_once __DIR__ . '/include/connection.php';

// Initialize variables
$items = [];
$total = 0.0;
$user_id = isset($_SESSION['r_id']) ? (int)$_SESSION['r_id'] : 0;
$pageTitle = 'Shopping Cart';

// Start output buffering
ob_start();

try {
    // For logged-in users, fetch cart from database
    if ($user_id > 0) {
        $sql = "SELECT c.cart_id, c.product_id, p.name as product_name, c.product_price, 
                       c.quantity, (c.quantity * c.product_price) as total_price, 
                       p.image as product_image
                FROM cart c
                JOIN products p ON c.product_id = p.product_id
                WHERE c.user_id = ?";
                
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            throw new Exception('Failed to prepare statement: ' . $conn->error);
        }
        
        $stmt->bind_param("i", $user_id);
        if (!$stmt->execute()) {
            throw new Exception('Failed to execute query: ' . $stmt->error);
        }
        
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $img = $row['product_image'] ?? '';
            $imgSrc = 'img/272.jpg'; // Default image
            
            // Check for image in different locations
            $imagePaths = [
                "/project-batch/my_edits/img/product_dynamic/" . $img,
                "/project-batch/my_edits/images_admin/products/" . $img,
                $img // In case it's already a full path
            ];
            
            foreach ($imagePaths as $path) {
                if (!empty($img) && file_exists($_SERVER['DOCUMENT_ROOT'] . $path)) {
                    $imgSrc = $path;
                    break;
                }
            }

            $price = (float)($row['product_price'] ?? 0);
            $qty = (int)($row['quantity'] ?? 1);
            $subtotal = isset($row['total_price']) && $row['total_price'] !== null 
                ? (float)$row['total_price'] 
                : $price * $qty;
                
            $total += $subtotal;

            $items[] = [
                'source'     => 'db',
                'cart_id'    => (int)$row['cart_id'],
                'product_id' => (int)($row['product_id'] ?? 0),
                'name'       => htmlspecialchars($row['product_name'] ?? 'Unknown Product'),
                'price'      => $price,
                'qty'        => $qty,
                'subtotal'   => $subtotal,
                'image'      => $imgSrc,
            ];
        }
        $stmt->close();
    } 
    // For guests, use session cart
    elseif (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
        // Get product details for all items in cart in a single query
        if (!empty($_SESSION['cart'])) {
            $product_ids = array_keys($_SESSION['cart']);
            $placeholders = str_repeat('?,', count($product_ids) - 1) . '?';
            
            $sql = "SELECT product_id, name, price, image FROM products WHERE product_id IN ($placeholders)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param(str_repeat('i', count($product_ids)), ...$product_ids);
            $stmt->execute();
            $products_result = $stmt->get_result();
            $products = [];
            
            while ($product = $products_result->fetch_assoc()) {
                $products[$product['product_id']] = $product;
            }
            
            foreach ($_SESSION['cart'] as $pid => $cart_item) {
                $pid = (int)$pid;
                if (!isset($products[$pid])) continue;
                
                $product = $products[$pid];
                $price = (float)$product['price'];
                $qty = (int)($cart_item['qty'] ?? 1);
                $subtotal = $price * $qty;
                $total += $subtotal;
                
                // Handle product image
                $img = $product['image'] ?? '';
                $imgSrc = 'img/272.jpg'; // Default image
                
                $imagePaths = [
                    "/project-batch/my_edits/img/product_dynamic/" . $img,
                    "/project-batch/my_edits/images_admin/products/" . $img,
                    $img // In case it's already a full path
                ];
                
                foreach ($imagePaths as $path) {
                    if (!empty($img) && file_exists($_SERVER['DOCUMENT_ROOT'] . $path)) {
                        $imgSrc = $path;
                        break;
                    }
                }

                $items[] = [
                    'source'     => 'session',
                    'cart_id'    => null,
                    'product_id' => $pid,
                    'name'       => htmlspecialchars($product['name'] ?? 'Unknown Product'),
                    'price'      => $price,
                    'qty'        => $qty,
                    'subtotal'   => $subtotal,
                    'image'      => $imgSrc,
                ];
            }
        }
    }
} catch (Exception $e) {
    error_log('Shopping Cart Error: ' . $e->getMessage());
    $_SESSION['error'] = 'An error occurred while loading your cart. Please try again.';
}

// Get the buffered content
$content = ob_get_clean();

// Now include the header
include __DIR__ . '/include/header.php';

// Display success/error messages
if (isset($_SESSION['success'])) {
    echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['success']) . '</div>';
    unset($_SESSION['success']);
}

if (isset($_SESSION['error'])) {
    echo '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['error']) . '</div>';
    unset($_SESSION['error']);
}

// Output the buffered content
echo $content;
?>

<div class="cart-container container py-4">
  <h2 class="cart-title mb-3">🛒 Your Shopping Cart</h2>

  <?php if(empty($items)): ?>
    <div class="cart-empty text-center p-5 bg-white shadow-sm rounded">
      <img src="https://cdn-icons-png.flaticon.com/512/11329/11329060.png" width="96" height="96" alt="Empty Cart" class="mb-3">
      <p class="mb-3 text-muted">Your cart is empty.</p>
      <a href="products.php" class="btn btn-primary btn-sm">Continue Shopping</a>
    </div>
  <?php else: ?>
    <div class="table-responsive cart-table-wrap">
      <table class="cart-table table table-striped align-middle bg-white shadow-sm rounded overflow-hidden">
        <thead class="thead-light">
          <tr>
            <th>Image</th>
            <th>Product</th>
            <th>Price</th>
            <th>Qty</th>
            <th>Subtotal</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>

      <?php foreach ($items as $row): ?>
      <tr>
        <td class="py-3"><img src="<?= $row['image'] ?>" width="72" height="72" style="object-fit:cover;border-radius:8px" alt="<?= htmlspecialchars($row['name']) ?>"></td>
        <td class="py-3"><strong><?= htmlspecialchars($row['name']) ?></strong></td>
        <td class="py-3">₹<?= number_format((float)$row['price'], 2) ?></td>
        <td class="py-3"><?= (int)$row['qty'] ?></td>
        <td class="py-3">₹<?= number_format((float)$row['subtotal'], 2) ?></td>
        <td class="py-3">
          <?php if ($row['source'] === 'db'): ?>
            <a href="<?php echo htmlspecialchars("remove_cart.php?id=" . (int)$row['cart_id']) ?>" 
               class="btn btn-link text-danger p-0 remove-btn"
               onclick="return confirm('Are you sure you want to remove this item?')">
              Remove
            </a>
          <?php else: ?>
            <a href="<?php echo htmlspecialchars("remove_cart.php?sid=" . (int)$row['product_id']) ?>" 
               class="btn btn-link text-danger p-0 remove-btn"
               onclick="return confirm('Are you sure you want to remove this item?')">
              Remove
            </a>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="cart-summary d-flex align-items-center justify-content-between bg-white shadow-sm rounded p-3 mt-3">
      <span class="h6 mb-0">Total: ₹<?= number_format($total, 2) ?></span>
      <a class="btn btn-success checkout-btn" href="checkout.php">Proceed to Checkout</a>
    </div>
  <?php endif; ?>
</div>

<?php
include('include/footer.php');
?>