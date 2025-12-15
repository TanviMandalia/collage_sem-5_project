<?php
require_once __DIR__ . '/process_order.php';

$page_title = 'My Orders';
include 'include/header.php';

// Check if user is logged in
if (!isset($_SESSION['r_id'])) {
    redirectWithMessage('login.php', 'error', 'Please login to view your orders');
}

$user_id = (int)$_SESSION['r_id'];
$orders = getUserOrders($conn, $user_id);
?>

<div class="container mt-5">
    <h2 class="mb-4">My Orders</h2>
    
    <?php if (empty($orders)): ?>
        <div class="alert alert-info">
            You haven't placed any orders yet. <a href="products.php">Start shopping</a>!
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Order #</th>
                        <th>Date</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>#<?php echo htmlspecialchars($order['order_id']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                            <td>
                                <?php 
                                    $order_details = getOrderDetails($conn, $order['order_id'], $user_id);
                                    echo $order_details ? count($order_details['items']) : 0; 
                                ?>
                            </td>
                            <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                            <td>
                                <span class="badge 
                                    <?php 
                                        switch($order['status']) {
                                            case 'Approved':
                                                echo 'bg-success';
                                                break;
                                            case 'Shipped':
                                                echo 'bg-primary';
                                                break;
                                            case 'Cancelled':
                                                echo 'bg-danger';
                                                break;
                                            default:
                                                echo 'bg-warning text-dark';
                                        }
                                    ?>">
                                    <?php echo htmlspecialchars($order['status']); ?>
                                </span>
                            </td>
                            <td>
                                <a href="view_order.php?id=<?php echo $order['order_id']; ?>" class="btn btn-sm btn-primary">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <?php if ($order['status'] === 'Pending'): ?>
                                    <a href="cancel_order.php?id=<?php echo $order['order_id']; ?>" 
                                       class="btn btn-sm btn-danger"
                                       onclick="return confirm('Are you sure you want to cancel this order?');">
                                        <i class="fas fa-times"></i> Cancel
                                    </a>
                                <?php endif; ?>
                                <?php if ($order['status'] === 'Shipped' && !$order['received']): ?>
                                    <a href="mark_received.php?id=<?php echo $order['order_id']; ?>" 
                                       class="btn btn-sm btn-success"
                                       onclick="return confirm('Mark this order as received?');">
                                        <i class="fas fa-check"></i> Received
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php include 'include/footer.php'; ?>
