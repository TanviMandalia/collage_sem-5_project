<?php
require_once __DIR__ . '/process_order.php';

$page_title = 'Order Details';
include 'include/header.php';

// Check if user is logged in
if (!isset($_SESSION['r_id'])) {
    redirectWithMessage('login.php', 'error', 'Please login to view order details');
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirectWithMessage('order_history.php', 'error', 'Invalid order ID');
}

$order_id = (int)$_GET['id'];
$user_id = (int)$_SESSION['r_id'];
$order = getOrderDetails($conn, $order_id, $user_id);

if (!$order) {
    redirectWithMessage('order_history.php', 'error', 'Order not found or you do not have permission to view it');
}
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Order #<?php echo $order['order_id']; ?></h2>
        <a href="order_history.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back to Orders
        </a>
    </div>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Order Items</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($order['items'] as $item): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?php if (!empty($item['product_image'])): ?>
                                                    <img src="<?php echo htmlspecialchars($item['product_image']); ?>" 
                                                         alt="<?php echo htmlspecialchars($item['product_name']); ?>" 
                                                         class="img-thumbnail me-3" style="width: 60px; height: 60px; object-fit: cover;">
                                                <?php endif; ?>
                                                <div>
                                                    <h6 class="mb-0"><?php echo htmlspecialchars($item['product_name']); ?></h6>
                                                    <small class="text-muted">SKU: <?php echo $item['product_id']; ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>$<?php echo number_format($item['price'], 2); ?></td>
                                        <td><?php echo $item['quantity']; ?></td>
                                        <td>$<?php echo number_format($item['subtotal'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Order Summary</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6>Order Status</h6>
                        <div class="d-flex align-items-center">
                            <div class="me-2">
                                <?php 
                                    $status_icon = 'clock';
                                    $status_class = 'text-warning';
                                    
                                    switch($order['status']) {
                                        case 'Approved':
                                            $status_icon = 'check-circle';
                                            $status_class = 'text-success';
                                            break;
                                        case 'Shipped':
                                            $status_icon = 'truck';
                                            $status_class = 'text-primary';
                                            break;
                                        case 'Cancelled':
                                            $status_icon = 'times-circle';
                                            $status_class = 'text-danger';
                                            break;
                                    }
                                ?>
                                <i class="fas fa-<?php echo $status_icon; ?> fa-2x <?php echo $status_class; ?>"></i>
                            </div>
                            <div>
                                <h5 class="mb-0"><?php echo htmlspecialchars($order['status']); ?></h5>
                                <small class="text-muted">
                                    <?php 
                                        if ($order['status'] === 'Shipped' && $order['received']) {
                                            echo 'Delivered on ' . date('M d, Y', strtotime($order['order_date'] . ' + 3 days'));
                                        } else {
                                            echo 'Order placed on ' . date('M d, Y', strtotime($order['order_date']));
                                        }
                                    ?>
                                </small>
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="mb-3">
                        <h6>Shipping Address</h6>
                        <address class="mb-0">
                            <?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?>
                        </address>
                    </div>
                    
                    <hr>
                    
                    <div class="mb-3">
                        <h6>Payment Method</h6>
                        <p class="mb-0">
                            <i class="fas fa-credit-card me-2"></i>
                            <?php echo htmlspecialchars($order['payment_method']); ?>
                        </p>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal:</span>
                        <span>$<?php echo number_format($order['total_amount'], 2); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Shipping:</span>
                        <span>$0.00</span>
                    </div>
                    <div class="d-flex justify-content-between fw-bold">
                        <span>Total:</span>
                        <span>$<?php echo number_format($order['total_amount'], 2); ?></span>
                    </div>
                </div>
                <div class="card-footer bg-light">
                    <?php if ($order['status'] === 'Pending'): ?>
                        <a href="cancel_order.php?id=<?php echo $order_id; ?>" 
                           class="btn btn-danger w-100"
                           onclick="return confirm('Are you sure you want to cancel this order?');">
                            <i class="fas fa-times"></i> Cancel Order
                        </a>
                    <?php elseif ($order['status'] === 'Shipped' && !$order['received']): ?>
                        <a href="mark_received.php?id=<?php echo $order_id; ?>" 
                           class="btn btn-success w-100"
                           onclick="return confirm('Mark this order as received?');">
                            <i class="fas fa-check"></i> Mark as Received
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Need Help?</h5>
                </div>
                <div class="card-body">
                    <p class="mb-2">
                        <i class="fas fa-phone-alt me-2"></i>
                        <a href="tel:+1234567890" class="text-decoration-none">+1 (234) 567-890</a>
                    </p>
                    <p class="mb-0">
                        <i class="fas fa-envelope me-2"></i>
                        <a href="mailto:support@example.com" class="text-decoration-none">support@example.com</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'include/footer.php'; ?>
