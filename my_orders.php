<?php
session_start();
require_once __DIR__ . '/include/connection.php';

if (empty($_SESSION['r_id'])) {
  $ret = $_SERVER['REQUEST_URI'] ?? 'my_orders.php';
  header('Location: registation.php?redirect=' . urlencode($ret));
  exit();
}

$user_id = (int)$_SESSION['r_id'];

$orders = mysqli_query($conn, "SELECT * FROM orders WHERE user_id = $user_id ORDER BY order_id DESC");

include __DIR__ . '/include/header.php';
?>
<div class="container py-4">
  <h2 class="mb-3">My Orders</h2>

  <?php if (!$orders || mysqli_num_rows($orders) === 0): ?>
    <div class="alert alert-info">You have no orders yet. <a href="products.php">Start shopping</a>.</div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table table-striped table-bordered">
        <thead>
          <tr>
            <th>#</th>
            <th>Date</th>
            <th>Status</th>
            <th>Total</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($o = mysqli_fetch_assoc($orders)): ?>
            <?php
              $total = isset($o['total_amount']) ? (float)$o['total_amount'] : (isset($o['order_total']) ? (float)$o['order_total'] : 0.0);
              $date  = !empty($o['order_date']) ? date('d M Y, h:i A', strtotime($o['order_date'])) : '';
              $status = isset($o['status']) ? $o['status'] : '';
            ?>
            <tr>
              <td>#<?php echo (int)$o['order_id']; ?></td>
              <td><?php echo htmlspecialchars($date); ?></td>
              <td>
                <?php if (strtolower($status) === 'completed'): ?>
                  <span class="badge badge-success">Completed</span>
                <?php elseif (strtolower($status) === 'pending'): ?>
                  <span class="badge badge-warning">Pending</span>
                <?php elseif (strtolower($status) === 'cancelled'): ?>
                  <span class="badge badge-danger">Cancelled</span>
                <?php elseif (strtolower($status) === 'shipped'): ?>
                  <span class="badge badge-info">Shipped</span>
                <?php else: ?>
                  <span class="badge badge-secondary"><?php echo htmlspecialchars($status); ?></span>
                <?php endif; ?>
              </td>
              <td>₹<?php echo number_format($total, 2); ?></td>
              <td>
                <a class="btn btn-sm btn-outline-primary" href="order_success.php?order_id=<?php echo (int)$o['order_id']; ?>">View</a>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>
<?php include __DIR__ . '/include/footer.php'; ?>
